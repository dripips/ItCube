<?php

namespace App\Services;

use Illuminate\Http\Client\Pool;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Запуск ученического кода.
 *
 * Своей песочницы у ItCube нет и не планируется: чужой код исполняет Wandbox, на
 * нашей стороне остаётся только приём результата. Одна площадка вместо
 * нескольких выбрана не ради простоты — Go Playground не принимает stdin, а без
 * него не собрать ни одного тест-кейса с входными данными.
 *
 * Списки запрещённого ниже — не изоляция, а вежливость: они отсекают явную дурь
 * до сетевого запроса, чтобы ученик получил внятный ответ вместо таймаута.
 * Безопасность обеспечивает то, что код исполняется не у нас.
 */
final class CodeRunner
{
    public const MAX_CODE_LENGTH = 20_000;

    public const MAX_OUTPUT_LINES = 200;

    /**
     * Сборка на площадке идёт долго: Go занимает около двадцати секунд, Rust
     * бывает и дольше. Контрольная из пяти кейсов последовательно шла бы почти
     * две минуты, поэтому кейсы уходят пачкой в параллель.
     */
    private const MAX_PARALLEL = 4;

    private const TIMEOUT_SECONDS = 60;

    private const ENDPOINT = 'https://wandbox.org/api/compile.json';

    /**
     * Язык — то, что выбирает преподаватель; компилятор — что понимает Wandbox.
     * Версии приколочены намеренно: head меняется под ногами, и вчера принятая
     * работа завтра перестала бы собираться.
     *
     * @var array<string, string>
     */
    public const COMPILERS = [
        'python' => 'cpython-3.13.8',
        'javascript' => 'nodejs-20.17.0',
        'typescript' => 'typescript-5.6.2',
        'php' => 'php-8.3.12',
        'cpp' => 'gcc-13.2.0',
        'c' => 'gcc-13.2.0-c',
        'java' => 'openjdk-jdk-22+36',
        // dotnetcore на Wandbox не собирается вовсе, остаётся mono. Не-ASCII он
        // печатает только после Console.OutputEncoding = Encoding.UTF8 — эта
        // строка стоит в заготовке кода для задач на C#.
        'csharp' => 'mono-6.12.0.199',
        'go' => 'go-1.23.2',
        'rust' => 'rust-1.82.0',
        'ruby' => 'ruby-3.4.9',
        'pascal' => 'fpc-3.2.2',
        'sql' => 'sqlite-3.46.1',
        'bash' => 'bash',
    ];

    /**
     * Ключи Wandbox для набора флагов компилятора.
     *
     * @var array<string, string>
     */
    private const COMPILER_OPTIONS = [
        'cpp' => 'warning,gnu++17,boost-nothing-gcc-13.2.0',
        'c' => 'warning,c17',
        // Rust здесь нет намеренно: набор warning для него невалиден, и площадка
        // отвечает кодом 1 с пустым выводом вместо внятной ошибки.
    ];

    /** @var array<string, array<int, array{0: string, 1: string}>> */
    private const BLOCKED = [
        'bash' => [
            ['/:\s*\(\)\s*\{[^}]*\|[^}]*&/', 'Форк-бомба'],
            ['/\brm\s+(-[rRf]+\s+)*\//', 'Удаление системных путей запрещено'],
            ['/\b(shutdown|reboot|halt|poweroff)\b/', 'Управление системой запрещено'],
            ['/\b(mkfs|fdisk)\b|\bdd\s+if=/', 'Операции с дисками запрещены'],
            ['/\b(wget|curl)\s/', 'Сеть в песочнице недоступна'],
            ['/\b(ssh|scp|telnet|ftp|nc|ncat)\s/', 'Удалённый доступ запрещён'],
            ['/\b(apt|apt-get|yum|dnf|pip|npm|gem|composer)\s+(install|remove|update|upgrade)/', 'Установка пакетов запрещена'],
        ],
        'python' => [
            ['/\bos\.system\s*\(/', 'os.system() запрещён — выводите через print()'],
            ['/\bsubprocess\b/', 'Модуль subprocess в песочнице запрещён'],
            ['/\bos\.popen\s*\(/', 'os.popen() в песочнице запрещён'],
            ['/\bos\.exec/', 'os.exec*() в песочнице запрещён'],
            ['/\bshutil\.rmtree\s*\(\s*["\']\//', 'Удаление системных каталогов запрещено'],
        ],
        'javascript' => [
            ['/require\s*\(\s*["\']child_process["\']\s*\)/', 'child_process в песочнице запрещён'],
            ['/\bexecSync\b/', 'Запуск процессов запрещён'],
        ],
        'php' => [
            ['/\b(exec|shell_exec|system|passthru|proc_open|popen)\s*\(/', 'Запуск процессов в песочнице запрещён'],
        ],
    ];

    /**
     * @return array<int, string> список языков для выпадающего списка
     */
    public static function languages(): array
    {
        return array_keys(self::COMPILERS);
    }

    public static function supports(string $language): bool
    {
        return isset(self::COMPILERS[$language]);
    }

    public function run(string $code, string $language, string $stdin = ''): RunResult
    {
        return $this->runMany($code, $language, [$stdin])[0];
    }

    /**
     * Один и тот же код на нескольких входах.
     *
     * @param  list<string>  $stdins
     * @return list<RunResult> в том же порядке, что и входы
     */
    public function runMany(string $code, string $language, array $stdins): array
    {
        if ($stdins === []) {
            return [];
        }

        // Проверка относится к коду целиком, а не к отдельному входу: если код
        // отклонён, отклонены все кейсы разом и в сеть не уходит ничего.
        if ($rejection = $this->reject($code, $language)) {
            return array_fill(0, count($stdins), $rejection);
        }

        $results = [];

        foreach (array_chunk($stdins, self::MAX_PARALLEL, true) as $chunk) {
            $startedAt = microtime(true);

            $responses = Http::pool(fn (Pool $pool): array => array_map(
                fn (string $stdin) => $pool->timeout(self::TIMEOUT_SECONDS)
                    ->connectTimeout(10)
                    ->acceptJson()
                    ->asJson()
                    ->post(self::ENDPOINT, $this->payload($code, $language, $stdin)),
                array_values($chunk),
            ));

            // Пул отдаёт всё разом, поэтому отдельного времени на кейс нет:
            // делим общее на размер пачки, чтобы цифра хотя бы не врала в разы.
            $elapsedMs = (int) round((microtime(true) - $startedAt) * 1000 / max(1, count($chunk)));

            foreach (array_keys($chunk) as $offset => $originalIndex) {
                $results[$originalIndex] = $this->fromResponse($responses[$offset] ?? null, $language, $elapsedMs);
            }
        }

        ksort($results);

        return array_values($results);
    }

    /**
     * @return array<string, mixed>
     */
    private function payload(string $code, string $language, string $stdin): array
    {
        $payload = [
            'code' => $this->preprocess($code, $language),
            'compiler' => self::COMPILERS[$language],
            'stdin' => $stdin,
            'save' => false,
        ];

        if (isset(self::COMPILER_OPTIONS[$language])) {
            $payload['options'] = self::COMPILER_OPTIONS[$language];
        }

        if ($language === 'java') {
            $payload['runtime-option-raw'] = "-Dfile.encoding=UTF-8\n-Dstdout.encoding=UTF-8";
        }

        return $payload;
    }

    /** Отказ до отправки — или null, если код можно отправлять. */
    private function reject(string $code, string $language): ?RunResult
    {
        if (trim($code) === '') {
            return RunResult::blocked(__('Код пустой'));
        }

        if (mb_strlen($code) > self::MAX_CODE_LENGTH) {
            return RunResult::blocked(__('Слишком длинный код: предел :max символов', ['max' => self::MAX_CODE_LENGTH]));
        }

        if (str_contains($code, "\0")) {
            return RunResult::blocked(__('В коде недопустимые символы'));
        }

        if (! self::supports($language)) {
            return RunResult::blocked(__('Язык :language не поддерживается', ['language' => $language]));
        }

        foreach (self::BLOCKED[$language] ?? [] as [$pattern, $message]) {
            if (preg_match($pattern, $code) === 1) {
                return RunResult::blocked(__($message));
            }
        }

        return null;
    }

    private function fromResponse(mixed $response, string $language, int $elapsedMs): RunResult
    {
        // Пул возвращает либо ответ, либо перехваченное исключение. Тип второго
        // не ограничен одним классом, и падать с TypeError из-за этого
        // приложение не должно — для ученика это всё равно «не получилось».
        if (! $response instanceof Response) {
            Log::warning('[CodeRunner] нет связи с площадкой', [
                'language' => $language,
                'error' => $response instanceof Throwable ? $response->getMessage() : null,
            ]);

            return RunResult::failed(__('Компилятор сейчас недоступен, попробуйте ещё раз'), $elapsedMs);
        }

        if (! $response->successful()) {
            Log::warning('[CodeRunner] площадка ответила ошибкой', [
                'language' => $language,
                'status' => $response->status(),
            ]);

            return RunResult::failed(__('Компилятор ответил ошибкой (:status)', ['status' => $response->status()]), $elapsedMs);
        }

        return $this->interpret($response->json() ?? [], $elapsedMs);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function interpret(array $data, int $runtimeMs): RunResult
    {
        $stdout = trim((string) ($data['program_output'] ?? ''));
        $stderr = trim((string) ($data['program_error'] ?? ''));
        $compileError = trim((string) ($data['compiler_error'] ?? ''));
        $exitCode = (string) ($data['status'] ?? '0');

        if ($exitCode !== '0') {
            $message = match (true) {
                $stderr !== '' => $stderr,
                $compileError !== '' => $compileError,
                default => $stdout,
            };

            return RunResult::failed($this->sanitize($message), $runtimeMs);
        }

        // Предупреждения компилятора ученику полезны, но в вывод не попадают:
        // иначе сверка с ожидаемым ответом развалилась бы на пустом месте.
        return RunResult::completed($this->sanitize($stdout), $runtimeMs);
    }

    private function preprocess(string $code, string $language): string
    {
        // Wandbox собирает файл под своим именем, и public-класс с чужим именем
        // ломает сборку. Ученик про это знать не обязан.
        if ($language === 'java') {
            return preg_replace('/^(\s*)public\s+(class\s+\w+)/m', '$1$2', $code) ?? $code;
        }

        return $code;
    }

    private function sanitize(string $output): string
    {
        $output = preg_replace('#/home/\w+/#', '', $output) ?? $output;
        $output = str_replace('prog.', 'code.', $output);
        // Console.OutputEncoding в mono дописывает метку порядка байтов. Без
        // этой строки сверка с ожидаемым ответом падала бы на невидимом символе.
        $output = str_replace("\u{FEFF}", '', $output);

        $lines = preg_split('/\R/', $output) ?: [];

        if (count($lines) > self::MAX_OUTPUT_LINES) {
            $lines = array_slice($lines, 0, self::MAX_OUTPUT_LINES);
            $lines[] = __('… вывод обрезан');
        }

        return trim(implode("\n", $lines));
    }
}

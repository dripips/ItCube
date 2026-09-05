<?php

namespace Tests\Unit;

use App\Services\CodeRunner;
use App\Services\RunResult;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CodeRunnerTest extends TestCase
{
    private function runner(): CodeRunner
    {
        return new CodeRunner;
    }

    /** Ответ площадки, когда программа отработала нормально. */
    private function okResponse(string $stdout): array
    {
        return [
            'status' => '0',
            'program_output' => $stdout,
            'program_error' => '',
            'compiler_error' => '',
        ];
    }

    #[Test]
    public function пустой_код_до_площадки_не_доезжает(): void
    {
        Http::fake();

        $result = $this->runner()->run('   ', 'python');

        $this->assertSame(RunResult::STATUS_BLOCKED, $result->status);
        Http::assertNothingSent();
    }

    #[Test]
    public function слишком_длинный_код_отклоняется(): void
    {
        Http::fake();

        $result = $this->runner()->run(str_repeat('x', CodeRunner::MAX_CODE_LENGTH + 1), 'python');

        $this->assertSame(RunResult::STATUS_BLOCKED, $result->status);
        Http::assertNothingSent();
    }

    #[Test]
    public function неизвестный_язык_отклоняется(): void
    {
        Http::fake();

        $result = $this->runner()->run('print(1)', 'brainfuck');

        $this->assertSame(RunResult::STATUS_BLOCKED, $result->status);
        Http::assertNothingSent();
    }

    #[Test]
    public function запрещённая_конструкция_отсекается_до_запроса(): void
    {
        Http::fake();

        $result = $this->runner()->run("import subprocess\nsubprocess.run(['ls'])", 'python');

        $this->assertSame(RunResult::STATUS_BLOCKED, $result->status);
        $this->assertStringContainsString('subprocess', $result->output);
        Http::assertNothingSent();
    }

    #[Test]
    public function отправляет_приколоченную_версию_компилятора_и_stdin(): void
    {
        Http::fake(['wandbox.org/*' => Http::response($this->okResponse('42'))]);

        $this->runner()->run('print(int(input()) + 2)', 'python', "40\n");

        Http::assertSent(function (Request $request): bool {
            $body = $request->data();

            return $body['compiler'] === CodeRunner::COMPILERS['python']
                && $body['stdin'] === "40\n"
                && $body['save'] === false;
        });
    }

    #[Test]
    public function успешный_ответ_превращается_в_вывод(): void
    {
        Http::fake(['wandbox.org/*' => Http::response($this->okResponse("привет\n"))]);

        $result = $this->runner()->run("print('привет')", 'python');

        $this->assertTrue($result->success);
        $this->assertSame('привет', $result->output);
        $this->assertSame(RunResult::STATUS_COMPLETED, $result->status);
    }

    #[Test]
    public function ошибка_сборки_доходит_до_ученика_текстом(): void
    {
        Http::fake(['wandbox.org/*' => Http::response([
            'status' => '1',
            'program_output' => '',
            'program_error' => '',
            'compiler_error' => "code.cc:1:13: error: expected ';'",
        ])]);

        $result = $this->runner()->run('int main(){ }', 'cpp');

        $this->assertFalse($result->success);
        $this->assertStringContainsString("expected ';'", $result->output);
    }

    #[Test]
    public function падение_в_рантайме_считается_неудачей(): void
    {
        Http::fake(['wandbox.org/*' => Http::response([
            'status' => '1',
            'program_output' => '',
            'program_error' => 'ValueError: упс',
            'compiler_error' => '',
        ])]);

        $result = $this->runner()->run("raise ValueError('упс')", 'python');

        $this->assertFalse($result->success);
        $this->assertStringContainsString('упс', $result->output);
    }

    #[Test]
    public function метка_порядка_байтов_из_mono_вырезается(): void
    {
        Http::fake(['wandbox.org/*' => Http::response($this->okResponse("\u{FEFF}привет"))]);

        $result = $this->runner()->run('class P{}', 'csharp');

        $this->assertSame('привет', $result->output);
    }

    #[Test]
    public function длинный_вывод_обрезается(): void
    {
        $flood = implode("\n", array_map(fn (int $i): string => "строка $i", range(1, 500)));
        Http::fake(['wandbox.org/*' => Http::response($this->okResponse($flood))]);

        $result = $this->runner()->run('x', 'python');

        $lines = explode("\n", $result->output);
        $this->assertCount(CodeRunner::MAX_OUTPUT_LINES + 1, $lines);
        $this->assertStringContainsString('обрезан', end($lines));
    }

    #[Test]
    public function недоступная_площадка_не_роняет_приложение(): void
    {
        Http::fake(['wandbox.org/*' => Http::failedConnection()]);

        $result = $this->runner()->run("print('привет')", 'python');

        $this->assertFalse($result->success);
        $this->assertSame(RunResult::STATUS_ERROR, $result->status);
    }

    #[Test]
    public function ответ_с_кодом_ошибки_не_считается_выводом(): void
    {
        Http::fake(['wandbox.org/*' => Http::response('слишком много запросов', 429)]);

        $result = $this->runner()->run("print('привет')", 'python');

        $this->assertFalse($result->success);
        $this->assertStringContainsString('429', $result->output);
    }

    #[Test]
    public function у_java_снимается_public_с_класса(): void
    {
        Http::fake(['wandbox.org/*' => Http::response($this->okResponse('ок'))]);

        $this->runner()->run('public class Solution { }', 'java');

        Http::assertSent(fn (Request $request): bool => str_contains($request->data()['code'], 'class Solution')
            && ! str_contains($request->data()['code'], 'public class'));
    }

    #[Test]
    public function для_rust_флаги_компилятора_не_отправляются(): void
    {
        // Набор warning для Rust невалиден: площадка молча отвечает кодом 1.
        Http::fake(['wandbox.org/*' => Http::response($this->okResponse('ок'))]);

        $this->runner()->run('fn main(){}', 'rust');

        Http::assertSent(fn (Request $request): bool => ! array_key_exists('options', $request->data()));
    }

    #[Test]
    public function несколько_входов_возвращаются_в_исходном_порядке(): void
    {
        $seen = 0;
        Http::fake(function () use (&$seen) {
            return Http::response($this->okResponse('ответ '.(++$seen)));
        });

        // Больше, чем размер пачки: важно, что порядок держится и между пачками.
        $results = $this->runner()->runMany('print(input())', 'python', ['1', '2', '3', '4', '5', '6']);

        $this->assertCount(6, $results);
        $this->assertSame(
            ['ответ 1', 'ответ 2', 'ответ 3', 'ответ 4', 'ответ 5', 'ответ 6'],
            array_map(fn (RunResult $r): string => $r->output, $results),
        );
    }

    #[Test]
    public function отклонённый_код_отклоняет_все_входы_сразу(): void
    {
        Http::fake();

        $results = $this->runner()->runMany('import subprocess', 'python', ['1', '2', '3']);

        $this->assertCount(3, $results);
        foreach ($results as $result) {
            $this->assertSame(RunResult::STATUS_BLOCKED, $result->status);
        }
        Http::assertNothingSent();
    }

    #[Test]
    public function пустой_список_входов_не_ходит_в_сеть(): void
    {
        Http::fake();

        $this->assertSame([], $this->runner()->runMany('print(1)', 'python', []));
        Http::assertNothingSent();
    }
}

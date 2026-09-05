<?php

namespace App\Console\Commands;

use App\Support\Locales;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

/**
 * Сверка словарей с тем, что на самом деле есть в коде.
 *
 * Ключ перевода — сама русская строка, поэтому «недостающее» и «лишнее»
 * считаются механически: обошли шаблоны и классы, собрали все строки внутри
 * __() и trans_choice(), сравнили с файлом языка.
 */
class LangCheck extends Command
{
    protected $signature = 'lang:check {--fill : дописать недостающие строки как непереведённые}';

    protected $description = 'Проверить словари на пропуски и осиротевшие строки';

    public function handle(): int
    {
        $sources = $this->collect();
        $this->info('Строк в коде: '.count($sources));

        $broken = false;

        foreach (array_keys(Locales::AVAILABLE) as $locale) {
            if ($locale === Locales::SOURCE) {
                continue;
            }

            $path = lang_path("$locale.json");
            $dictionary = File::exists($path)
                ? json_decode(File::get($path), true) ?: []
                : [];

            $missing = array_values(array_diff($sources, array_keys($dictionary)));
            $orphans = array_values(array_diff(array_keys($dictionary), $sources));

            $this->line(sprintf(
                '%s: переведено %d из %d, лишних %d',
                $locale,
                count($dictionary) - count($orphans),
                count($sources),
                count($orphans),
            ));

            foreach (array_slice($missing, 0, 15) as $line) {
                $this->line('  нет перевода: '.$line);
            }

            if (count($missing) > 15) {
                $this->line('  … и ещё '.(count($missing) - 15));
            }

            foreach (array_slice($orphans, 0, 10) as $line) {
                $this->line('  лишняя строка: '.$line);
            }

            if ($this->option('fill') && $missing !== []) {
                foreach ($missing as $line) {
                    $dictionary[$line] = '';
                }
                ksort($dictionary);
                File::put($path, json_encode($dictionary, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)."\n");
                $this->info("  дописано в $locale.json: ".count($missing));
            }

            $broken = $broken || $missing !== [] || $orphans !== [];
        }

        return $broken && ! $this->option('fill') ? self::FAILURE : self::SUCCESS;
    }

    /**
     * Все переводимые строки из шаблонов и классов.
     *
     * @return list<string>
     */
    public function collect(): array
    {
        $found = [];

        foreach (['resources/views', 'app'] as $directory) {
            foreach (File::allFiles(base_path($directory)) as $file) {
                if (! in_array($file->getExtension(), ['php'], true)) {
                    continue;
                }

                $body = $file->getContents();

                // Одинарные кавычки для __() и trans_choice(): двойные в этом
                // проекте не используются, а разбор обеих форм ловил бы строки
                // с интерполяцией, которые переводить нельзя.
                preg_match_all("/(?:__|trans_choice)\(\s*'((?:[^'\\\\]|\\\\.)*)'/", $body, $matches);

                foreach ($matches[1] as $line) {
                    $found[stripcslashes($line)] = true;
                }
            }
        }

        $keys = array_keys($found);
        sort($keys);

        return $keys;
    }
}

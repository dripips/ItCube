<?php

namespace Tests\Feature;

use App\Console\Commands\LangCheck;
use App\Support\Locales;
use Illuminate\Support\Facades\File;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Словари должны покрывать то, что есть в коде, а не то, что было когда-то.
 *
 * Без этого теста язык разъезжается тихо: непереведённая строка остаётся
 * русской и на английской странице выглядит как опечатка, а не как пропуск.
 */
class LocalisationTest extends TestCase
{
    /** @return list<string> */
    private function sources(): array
    {
        return (new LangCheck)->collect();
    }

    /** @return array<string, string> */
    private function dictionary(string $locale): array
    {
        $path = lang_path("$locale.json");
        $this->assertFileExists($path);

        return json_decode(File::get($path), true) ?? [];
    }

    #[Test]
    public function словари_покрывают_все_строки(): void
    {
        $sources = $this->sources();
        $this->assertNotEmpty($sources, 'извлекатель не нашёл ни одной строки — сломан разбор');

        foreach (array_keys(Locales::AVAILABLE) as $locale) {
            if ($locale === Locales::SOURCE) {
                continue;
            }

            $missing = array_diff($sources, array_keys($this->dictionary($locale)));

            $this->assertSame([], array_values($missing), "в $locale.json нет перевода для строк");
        }
    }

    #[Test]
    public function в_словарях_нет_осиротевших_строк(): void
    {
        $sources = $this->sources();

        foreach (array_keys(Locales::AVAILABLE) as $locale) {
            if ($locale === Locales::SOURCE) {
                continue;
            }

            $orphans = array_diff(array_keys($this->dictionary($locale)), $sources);

            $this->assertSame([], array_values($orphans), "в $locale.json остались строки, которых больше нет в коде");
        }
    }

    #[Test]
    public function ни_один_перевод_не_пустой(): void
    {
        foreach (array_keys(Locales::AVAILABLE) as $locale) {
            if ($locale === Locales::SOURCE) {
                continue;
            }

            foreach ($this->dictionary($locale) as $source => $translation) {
                $this->assertNotSame('', trim($translation), "пустой перевод в $locale.json: $source");
            }
        }
    }

    #[Test]
    public function подстановки_сохраняются_в_переводе(): void
    {
        // :count, :max, :n и прочее должны дожить до перевода: без них строка
        // напечатается без числа, и никто этого не заметит до продакшена.
        foreach (array_keys(Locales::AVAILABLE) as $locale) {
            if ($locale === Locales::SOURCE) {
                continue;
            }

            foreach ($this->dictionary($locale) as $source => $translation) {
                preg_match_all('/:[a-z]+/', $source, $expected);
                preg_match_all('/:[a-z]+/', $translation, $actual);

                $this->assertSame(
                    array_unique($expected[0]),
                    array_unique($actual[0]),
                    "в $locale.json потеряна подстановка: $source",
                );
            }
        }
    }

    #[Test]
    public function число_форм_множественного_соответствует_языку(): void
    {
        // В русском три формы, в английском и немецком две. Лишняя форма не
        // сломает вывод, но выдаст перевод, сделанный копированием.
        $expected = ['en' => 2, 'de' => 2];

        foreach ($expected as $locale => $forms) {
            foreach ($this->dictionary($locale) as $source => $translation) {
                if (! str_contains($source, '|')) {
                    continue;
                }

                $sourceForms = substr_count($source, '|') + 1;
                $translationForms = substr_count($translation, '|') + 1;

                // У строк с нулевой формой {0} на одну больше.
                $this->assertSame(
                    str_starts_with($source, '{0}') ? $forms + 1 : $forms,
                    $translationForms,
                    "в $locale.json у строки неверное число форм (в источнике $sourceForms): $source",
                );
            }
        }
    }

    #[Test]
    public function множественное_число_на_исходном_языке_не_уезжает_в_перевод(): void
    {
        // trans_choice() уходит в запасной язык там, где __() возвращает сам
        // ключ. Пока запасным был английский, русская страница показывала
        // английскую фразу ровно в тех местах, где счёт шёл через trans_choice.
        $this->assertSame('ru', config('app.fallback_locale'));

        app()->setLocale('ru');

        $this->assertSame(
            '3 вопроса',
            trans_choice('{1}:count вопрос|[2,4]:count вопроса|[5,*]:count вопросов', 3, ['count' => 3]),
        );

        app()->setLocale('en');

        $this->assertSame(
            '3 questions',
            trans_choice('{1}:count вопрос|[2,4]:count вопроса|[5,*]:count вопросов', 3, ['count' => 3]),
        );
    }

    #[Test]
    public function страница_переводится_целиком(): void
    {
        $this->post(route('locale'), ['locale' => 'en']);

        $this->get(route('login'))
            ->assertOk()
            ->assertSee('Sign in to ItCube')
            ->assertDontSee('Вход в ItCube');
    }

    #[Test]
    public function язык_берётся_из_заголовка_браузера(): void
    {
        $this->get(route('login'), ['Accept-Language' => 'de-DE,de;q=0.9,en;q=0.8'])
            ->assertOk()
            ->assertSee('Anmeldung bei ItCube');
    }

    #[Test]
    public function незнакомый_язык_в_заголовке_даёт_исходный(): void
    {
        $this->get(route('login'), ['Accept-Language' => 'ja-JP,ja;q=0.9'])
            ->assertOk()
            ->assertSee('Вход в ItCube');
    }
}

<?php

namespace App\Support;

/**
 * Языки интерфейса.
 *
 * Ключ перевода — сама русская строка, а не выдуманное имя вроде nav.groups.
 * Поэтому непереведённое место выглядит как обычный русский текст, а не как
 * `nav.groups` посреди страницы, и новый язык добавляется переводом одного
 * файла без единой правки в коде.
 */
final class Locales
{
    public const SOURCE = 'ru';

    /** @var array<string, string> код языка => как он называет себя сам */
    public const AVAILABLE = [
        'ru' => 'Русский',
        'en' => 'English',
        'de' => 'Deutsch',
    ];

    public static function supports(?string $locale): bool
    {
        return $locale !== null && isset(self::AVAILABLE[$locale]);
    }

    public static function name(string $locale): string
    {
        return self::AVAILABLE[$locale] ?? $locale;
    }

    /**
     * Первый подходящий язык из заголовка браузера.
     *
     * Разбор нарочно грубый: качество (q=) не учитывается, потому что порядок
     * в заголовке и так почти всегда совпадает с предпочтением.
     */
    public static function fromHeader(?string $header): string
    {
        foreach (explode(',', (string) $header) as $chunk) {
            $code = strtolower(trim(explode(';', $chunk)[0]));

            if (self::supports($code)) {
                return $code;
            }

            $base = explode('-', $code)[0];

            if (self::supports($base)) {
                return $base;
            }
        }

        return self::SOURCE;
    }
}

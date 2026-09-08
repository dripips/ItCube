<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Тестовый клиент по умолчанию шлёт Accept-Language: en-us, и страницы
        // приходили бы на английском, хотя исходный язык проекта русский. Это
        // не ошибка приложения: middleware честно уважает заголовок. Тесты
        // работают на исходном языке, а те, что проверяют перевод, задают
        // заголовок сами.
        $this->withHeader('Accept-Language', 'ru');
    }

    //
}

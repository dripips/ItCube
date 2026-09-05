<?php

namespace App\Enums;

enum SubmissionStatus: string
{
    case Passed = 'passed';
    case Failed = 'failed';
    case Error = 'error';
    case Blocked = 'blocked';

    public function label(): string
    {
        return match ($this) {
            self::Passed => __('Зачтено'),
            self::Failed => __('Тесты не прошли'),
            self::Error => __('Ошибка выполнения'),
            self::Blocked => __('Отклонено до запуска'),
        };
    }
}

<?php

namespace App\Enums;

enum SubmissionStatus: string
{
    case Pending = 'pending';
    case Passed = 'passed';
    case Failed = 'failed';
    case Error = 'error';
    case Blocked = 'blocked';

    public function label(): string
    {
        return match ($this) {
            self::Pending => __('Проверяется'),
            self::Passed => __('Зачтено'),
            self::Failed => __('Тесты не прошли'),
            self::Error => __('Ошибка выполнения'),
            self::Blocked => __('Отклонено до запуска'),
        };
    }

    /** Проверка ещё идёт: страница должна опрашивать результат. */
    public function isPending(): bool
    {
        return $this === self::Pending;
    }
}

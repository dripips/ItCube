<?php

namespace App\Enums;

enum Role: string
{
    case Admin = 'admin';
    case Teacher = 'teacher';
    case Student = 'student';

    public function label(): string
    {
        return match ($this) {
            self::Admin => __('Администратор'),
            self::Teacher => __('Преподаватель'),
            self::Student => __('Ученик'),
        };
    }

    /** Кто может вести занятия и проверять работы. */
    public function teaches(): bool
    {
        return $this === self::Teacher || $this === self::Admin;
    }
}

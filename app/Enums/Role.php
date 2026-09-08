<?php

namespace App\Enums;

enum Role: string
{
    case Admin = 'admin';
    case Teacher = 'teacher';
    case Student = 'student';
    case Guardian = 'guardian';

    public function label(): string
    {
        return match ($this) {
            self::Admin => __('Администратор'),
            self::Teacher => __('Преподаватель'),
            self::Student => __('Ученик'),
            self::Guardian => __('Родитель'),
        };
    }

    /** Кто может вести занятия и проверять работы. */
    public function teaches(): bool
    {
        return $this === self::Teacher || $this === self::Admin;
    }

    /** Роли, которые заводит администратор в панели управления. */
    public static function assignable(): array
    {
        return [self::Student, self::Guardian, self::Teacher, self::Admin];
    }
}

<?php

namespace App\Enums;

enum AttendanceStatus: string
{
    case Present = 'present';
    case Absent = 'absent';
    case Late = 'late';
    case Excused = 'excused';

    public function label(): string
    {
        return match ($this) {
            self::Present => __('Был'),
            self::Absent => __('Не был'),
            self::Late => __('Опоздал'),
            self::Excused => __('По уважительной'),
        };
    }

    /** Пропуск по уважительной в проценте посещаемости не штрафует. */
    public function countsAsAttended(): bool
    {
        return $this !== self::Absent;
    }
}

<?php

namespace App\Enums;

enum AttendanceStatus: string
{
    case Present = 'present';
    case Absent = 'absent';
    case Late = 'late';
    case Excused = 'excused';

    /**
     * Подписи назывные, а не глагольные: «Был» в карточке девочки — ошибка,
     * а рода у отметки в журнале нет и быть не может.
     */
    public function label(): string
    {
        return match ($this) {
            self::Present => __('На занятии'),
            self::Absent => __('Пропуск'),
            self::Late => __('Опоздание'),
            self::Excused => __('По уважительной'),
        };
    }

    /** Пропуск по уважительной в проценте посещаемости не штрафует. */
    public function countsAsAttended(): bool
    {
        return $this !== self::Absent;
    }
}

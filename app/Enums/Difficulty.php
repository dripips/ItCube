<?php

namespace App\Enums;

enum Difficulty: string
{
    case Easy = 'easy';
    case Medium = 'medium';
    case Hard = 'hard';

    public function label(): string
    {
        return match ($this) {
            self::Easy => __('Просто'),
            self::Medium => __('Средне'),
            self::Hard => __('Сложно'),
        };
    }
}

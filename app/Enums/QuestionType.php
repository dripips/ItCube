<?php

namespace App\Enums;

enum QuestionType: string
{
    case Single = 'single';
    case Multiple = 'multiple';
    case Text = 'text';

    public function label(): string
    {
        return match ($this) {
            self::Single => __('Один ответ'),
            self::Multiple => __('Несколько ответов'),
            self::Text => __('Короткий ответ'),
        };
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Question extends Model
{
    /** @use HasFactory<\Database\Factories\QuestionFactory> */
    use HasFactory;

    /** @var list<string> */
    protected $fillable = [
        'quiz_id',
        'type',
        'text',
        'explanation',
        'points',
        'position',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'type' => \App\Enums\QuestionType::class,
        ];
    }

    public function quiz(): BelongsTo
    {
        return $this->belongsTo(Quiz::class);
    }

    public function options(): HasMany
    {
        return $this->hasMany(AnswerOption::class)->orderBy('position');
    }

    /** @return list<int> идентификаторы верных вариантов */
    public function correctOptionIds(): array
    {
        return $this->options()->where('is_correct', true)->pluck('id')->all();
    }
}

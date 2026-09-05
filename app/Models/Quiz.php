<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Quiz extends Model
{
    /** @use HasFactory<\Database\Factories\QuizFactory> */
    use HasFactory;

    /** @var list<string> */
    protected $fillable = [
        'lesson_id',
        'author_id',
        'title',
        'description',
        'time_limit_minutes',
        'attempts_allowed',
        'shuffle_questions',
        'published',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'shuffle_questions' => 'boolean',
            'published' => 'boolean',
        ];
    }

    public function lesson(): BelongsTo
    {
        return $this->belongsTo(Lesson::class);
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function questions(): HasMany
    {
        return $this->hasMany(Question::class)->orderBy('position');
    }

    public function attempts(): HasMany
    {
        return $this->hasMany(QuizAttempt::class);
    }

    /** @return MorphMany<AssessmentItem, $this> */
    public function assessmentItems(): MorphMany
    {
        return $this->morphMany(AssessmentItem::class, 'itemable');
    }

    public function maxScore(): int
    {
        return (int) $this->questions()->sum('points');
    }
}

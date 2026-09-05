<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class QuizAttempt extends Model
{
    /** @use HasFactory<\Database\Factories\QuizAttemptFactory> */
    use HasFactory;

    /** @var list<string> */
    protected $fillable = [
        'quiz_id',
        'user_id',
        'assessment_attempt_id',
        'started_at',
        'submitted_at',
        'score',
        'max_score',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'submitted_at' => 'datetime',
        ];
    }

    public function quiz(): BelongsTo
    {
        return $this->belongsTo(Quiz::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function assessmentAttempt(): BelongsTo
    {
        return $this->belongsTo(AssessmentAttempt::class, 'assessment_attempt_id');
    }

    public function answers(): HasMany
    {
        return $this->hasMany(QuizAnswer::class);
    }

    public function isFinished(): bool
    {
        return $this->submitted_at !== null;
    }

    /** Время вышло — попытка закрывается сама, даже если вкладку закрыли. */
    public function isExpired(): bool
    {
        $limit = $this->quiz?->time_limit_minutes;

        return $limit !== null && $this->started_at->addMinutes($limit)->isPast();
    }
}

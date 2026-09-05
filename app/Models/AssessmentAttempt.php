<?php

namespace App\Models;

use Database\Factories\AssessmentAttemptFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AssessmentAttempt extends Model
{
    /** @use HasFactory<AssessmentAttemptFactory> */
    use HasFactory;

    /** @var list<string> */
    protected $fillable = [
        'assessment_id',
        'user_id',
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

    public function assessment(): BelongsTo
    {
        return $this->belongsTo(Assessment::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function quizAttempts(): HasMany
    {
        return $this->hasMany(QuizAttempt::class);
    }

    /** Сколько минут осталось; null — когда ограничения нет. */
    public function minutesLeft(): ?int
    {
        $duration = $this->assessment?->duration_minutes;

        if ($duration === null) {
            return null;
        }

        return max(0, (int) ceil(now()->diffInSeconds($this->started_at->addMinutes($duration), false) / 60));
    }
}

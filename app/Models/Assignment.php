<?php

namespace App\Models;

use App\Enums\Difficulty;
use Database\Factories\AssignmentFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Assignment extends Model
{
    /** @use HasFactory<AssignmentFactory> */
    use HasFactory;

    /** @var list<string> */
    protected $fillable = [
        'lesson_id',
        'title',
        'slug',
        'instructions',
        'language',
        'starter_code',
        'solution_code',
        'hints',
        'difficulty',
        'position',
        'published',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'hints' => 'array',
            'published' => 'boolean',
            'difficulty' => Difficulty::class,
        ];
    }

    public function lesson(): BelongsTo
    {
        return $this->belongsTo(Lesson::class);
    }

    public function tests(): HasMany
    {
        return $this->hasMany(AssignmentTest::class)->orderBy('position');
    }

    public function submissions(): HasMany
    {
        return $this->hasMany(Submission::class);
    }

    /** @return MorphMany<AssessmentItem, $this> */
    public function assessmentItems(): MorphMany
    {
        return $this->morphMany(AssessmentItem::class, 'itemable');
    }

    /** Максимум баллов — сумма по всем кейсам, включая скрытые. */
    public function maxScore(): int
    {
        return (int) $this->tests()->sum('points');
    }

    public function bestSubmissionFor(User $student): ?Submission
    {
        return $this->submissions()
            ->where('user_id', $student->id)
            ->orderByDesc('score')
            ->orderBy('created_at')
            ->first();
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Assessment extends Model
{
    /** @use HasFactory<\Database\Factories\AssessmentFactory> */
    use HasFactory;

    /** @var list<string> */
    protected $fillable = [
        'group_id',
        'author_id',
        'title',
        'description',
        'opens_at',
        'closes_at',
        'duration_minutes',
        'published',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'opens_at' => 'datetime',
            'closes_at' => 'datetime',
            'published' => 'boolean',
        ];
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class);
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(AssessmentItem::class)->orderBy('position');
    }

    public function attempts(): HasMany
    {
        return $this->hasMany(AssessmentAttempt::class);
    }

    /** Окно сдачи открыто: опубликована, началась и ещё не закрылась. */
    public function isOpen(): bool
    {
        if (! $this->published) {
            return false;
        }

        if ($this->opens_at !== null && $this->opens_at->isFuture()) {
            return false;
        }

        return $this->closes_at === null || $this->closes_at->isFuture();
    }

    public function maxScore(): int
    {
        return (int) $this->items()->sum('points');
    }
}

<?php

namespace App\Models;

use Database\Factories\DirectionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Direction extends Model
{
    /** @use HasFactory<DirectionFactory> */
    use HasFactory;

    /** @var list<string> */
    protected $fillable = [
        'teacher_id',
        'name',
        'slug',
        'icon',
        'age_range',
        'description',
        'position',
        'published',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'published' => 'boolean',
        ];
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function subjects(): HasMany
    {
        return $this->hasMany(Subject::class)->orderBy('position');
    }

    public function groups(): HasMany
    {
        return $this->hasMany(Group::class);
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}

<?php

namespace App\Models;

use Database\Factories\AssignmentTestFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssignmentTest extends Model
{
    /** @use HasFactory<AssignmentTestFactory> */
    use HasFactory;

    /** @var list<string> */
    protected $fillable = [
        'assignment_id',
        'name',
        'stdin',
        'expected_output',
        'is_hidden',
        'points',
        'position',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'is_hidden' => 'boolean',
        ];
    }

    public function assignment(): BelongsTo
    {
        return $this->belongsTo(Assignment::class);
    }
}

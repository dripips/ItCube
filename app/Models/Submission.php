<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Submission extends Model
{
    /** @use HasFactory<\Database\Factories\SubmissionFactory> */
    use HasFactory;

    /** @var list<string> */
    protected $fillable = [
        'assignment_id',
        'user_id',
        'code',
        'status',
        'output',
        'test_results',
        'passed_count',
        'total_count',
        'score',
        'attempt_number',
        'runtime_ms',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'test_results' => 'array',
            'status' => \App\Enums\SubmissionStatus::class,
        ];
    }

    public function assignment(): BelongsTo
    {
        return $this->belongsTo(Assignment::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function passed(): bool
    {
        return $this->status === \App\Enums\SubmissionStatus::Passed;
    }
}

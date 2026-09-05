<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Attendance extends Model
{
    /** @use HasFactory<\Database\Factories\AttendanceFactory> */
    use HasFactory;

    /** @var list<string> */
    protected $fillable = [
        'group_id',
        'lesson_id',
        'user_id',
        'held_on',
        'status',
        'note',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'held_on' => 'date',
            'status' => \App\Enums\AttendanceStatus::class,
        ];
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class);
    }

    public function lesson(): BelongsTo
    {
        return $this->belongsTo(Lesson::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}

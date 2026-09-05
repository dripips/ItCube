<?php

namespace App\Models;

use App\Enums\Role;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * Преподаватель и ученик — одна таблица и одна форма входа. В версии 2023 года
 * это были две таблицы с двумя обработчиками входа, и всё, что касалось обоих
 * сразу, приходилось писать дважды.
 */
#[Fillable(['username', 'name', 'first_name', 'last_name', 'email', 'password', 'role', 'avatar_path', 'bio', 'locale', 'is_active'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => Role::class,
            'is_active' => 'boolean',
        ];
    }

    public function fullName(): string
    {
        $parts = array_filter([$this->last_name, $this->first_name]);

        return $parts === [] ? (string) ($this->name ?? $this->username) : implode(' ', $parts);
    }

    public function isTeacher(): bool
    {
        return $this->role->teaches();
    }

    public function isStudent(): bool
    {
        return $this->role === Role::Student;
    }

    public function isAdmin(): bool
    {
        return $this->role === Role::Admin;
    }

    /** Группы, в которых числится как ученик. @return BelongsToMany<Group, $this> */
    public function groups(): BelongsToMany
    {
        return $this->belongsToMany(Group::class)
            ->withPivot(['joined_on', 'left_on'])
            ->withTimestamps();
    }

    /** Группы, которые ведёт. @return HasMany<Group, $this> */
    public function taughtGroups(): HasMany
    {
        return $this->hasMany(Group::class, 'teacher_id');
    }

    /** @return HasMany<Direction, $this> */
    public function directions(): HasMany
    {
        return $this->hasMany(Direction::class, 'teacher_id');
    }

    /** @return HasMany<Submission, $this> */
    public function submissions(): HasMany
    {
        return $this->hasMany(Submission::class);
    }

    /** @return HasMany<QuizAttempt, $this> */
    public function quizAttempts(): HasMany
    {
        return $this->hasMany(QuizAttempt::class);
    }

    /** @return HasMany<Attendance, $this> */
    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    /** @return HasMany<AssessmentAttempt, $this> */
    public function assessmentAttempts(): HasMany
    {
        return $this->hasMany(AssessmentAttempt::class);
    }

    /**
     * Занятие доступно, если ученик числится хотя бы в одной группе того
     * направления, к которому занятие относится. Проверка идёт по группам, а не
     * по направлению напрямую: отчисленный из группы теряет доступ вместе с ней.
     */
    public function canSee(Lesson $lesson): bool
    {
        if ($this->isTeacher()) {
            return true;
        }

        return $this->groups()
            ->where('direction_id', $lesson->subject->direction_id)
            ->exists();
    }

    /** @return HasMany<File, $this> */
    public function files(): HasMany
    {
        return $this->hasMany(File::class);
    }
}

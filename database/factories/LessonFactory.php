<?php

namespace Database\Factories;

use App\Models\Lesson;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Lesson>
 */
class LessonFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'subject_id' => Subject::factory(),
            'teacher_id' => User::factory()->teacher(),
            'title' => fake()->sentence(3),
            'slug' => fake()->unique()->slug(3),
            'summary' => fake()->sentence(),
            'content' => fake()->paragraph(),
            'code_language' => 'python',
            'position' => 0,
            'published_at' => now()->subDay(),
        ];
    }
}

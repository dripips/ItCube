<?php

namespace Database\Factories;

use App\Models\Assignment;
use App\Enums\Difficulty;
use App\Models\Lesson;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Assignment>
 */
class AssignmentFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'lesson_id' => Lesson::factory(),
            'title' => fake()->sentence(3),
            'slug' => fake()->unique()->slug(3),
            'instructions' => fake()->paragraph(),
            'language' => 'python',
            'starter_code' => '',
            'difficulty' => Difficulty::Easy,
            'position' => 0,
            'published' => true,
        ];
    }
}

<?php

namespace Database\Factories;

use App\Models\Quiz;
use App\Models\Lesson;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Quiz>
 */
class QuizFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'lesson_id' => Lesson::factory(),
            'author_id' => User::factory()->teacher(),
            'title' => fake()->sentence(3),
            'attempts_allowed' => 1,
            'published' => true,
        ];
    }
}

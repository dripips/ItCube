<?php

namespace Database\Factories;

use App\Models\Question;
use App\Enums\QuestionType;
use App\Models\Quiz;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Question>
 */
class QuestionFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'quiz_id' => Quiz::factory(),
            'type' => QuestionType::Single,
            'text' => fake()->sentence().'?',
            'points' => 1,
            'position' => 0,
        ];
    }
}

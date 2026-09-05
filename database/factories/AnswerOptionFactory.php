<?php

namespace Database\Factories;

use App\Models\AnswerOption;
use App\Models\Question;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AnswerOption>
 */
class AnswerOptionFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'question_id' => Question::factory(),
            'text' => fake()->words(2, true),
            'is_correct' => false,
            'position' => 0,
        ];
    }

    public function correct(): static
    {
        return $this->state(fn (): array => ['is_correct' => true]);
    }
}

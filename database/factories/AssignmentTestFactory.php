<?php

namespace Database\Factories;

use App\Models\AssignmentTest;
use App\Models\Assignment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AssignmentTest>
 */
class AssignmentTestFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'assignment_id' => Assignment::factory(),
            'name' => null,
            'stdin' => '',
            'expected_output' => 'ок',
            'is_hidden' => false,
            'points' => 1,
            'position' => 0,
        ];
    }

    public function hidden(): static
    {
        return $this->state(fn (): array => ['is_hidden' => true]);
    }
}

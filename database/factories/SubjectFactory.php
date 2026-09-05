<?php

namespace Database\Factories;

use App\Models\Subject;
use App\Models\Direction;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Subject>
 */
class SubjectFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'direction_id' => Direction::factory(),
            'name' => fake()->words(2, true),
            'slug' => fake()->unique()->slug(2),
            'position' => 0,
        ];
    }
}

<?php

namespace Database\Factories;

use App\Models\Group;
use App\Models\Direction;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Group>
 */
class GroupFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'direction_id' => Direction::factory(),
            'teacher_id' => User::factory()->teacher(),
            'name' => 'Группа '.fake()->unique()->numberBetween(1, 9999),
            'slug' => fake()->unique()->slug(2),
            'starts_on' => now()->subMonth()->toDateString(),
            'is_archived' => false,
        ];
    }
}

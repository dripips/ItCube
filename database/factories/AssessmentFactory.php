<?php

namespace Database\Factories;

use App\Models\Assessment;
use App\Models\Group;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Assessment>
 */
class AssessmentFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'group_id' => Group::factory(),
            'author_id' => User::factory()->teacher(),
            'title' => 'Контрольная '.fake()->unique()->numberBetween(1, 999),
            'opens_at' => now()->subHour(),
            'closes_at' => now()->addDay(),
            'duration_minutes' => 45,
            'published' => true,
        ];
    }
}

<?php

namespace Database\Factories;

use App\Models\Direction;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Direction>
 */
class DirectionFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'teacher_id' => User::factory()->teacher(),
            'name' => fake()->randomElement(['Программирование', 'Робототехника', 'Мобильная разработка', 'Системное администрирование']).' '.fake()->unique()->numberBetween(1, 9999),
            'slug' => fake()->unique()->slug(2),
            'icon' => 'code',
            'age_range' => '12–17',
            'description' => fake()->sentence(),
            'position' => 0,
            'published' => true,
        ];
    }
}

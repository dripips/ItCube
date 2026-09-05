<?php

namespace Database\Factories;

use App\Models\Group;
use App\Models\Schedule;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Schedule>
 */
class ScheduleFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'group_id' => Group::factory(),
            'day_of_week' => fake()->numberBetween(1, 5),
            'starts_at' => '16:00',
            'ends_at' => '17:30',
        ];
    }
}

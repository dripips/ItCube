<?php

namespace Database\Factories;

use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Post>
 */
class PostFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory()->teacher(),
            'title' => fake()->sentence(4),
            'slug' => fake()->unique()->slug(3),
            'excerpt' => fake()->sentence(),
            'content' => fake()->paragraphs(3, true),
            'published_at' => now()->subDay(),
        ];
    }
}

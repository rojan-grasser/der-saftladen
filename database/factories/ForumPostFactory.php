<?php

namespace Database\Factories;

use App\Models\Topic;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ForumPost>
 */
class ForumPostFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'content' => fake()->paragraph(2),
            'user_id' => User::inRandomOrder()->first()?->id ?? User::factory(),
            'topic_id' => Topic::inRandomOrder()->first()?->id ?? Topic::factory(),
        ];
    }
}

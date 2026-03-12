<?php

namespace Database\Factories;

use App\Enums\PostReactionType;
use App\Models\ForumPost;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PostReaction>
 */
class PostReactionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::inRandomOrder()->first()?->id ?? User::factory(),
            'forum_post_id' => ForumPost::inRandomOrder()->first()?->id ?? ForumPost::factory(),
            'type' => PostReactionType::LIKE,
        ];
    }
}

<?php

namespace Database\Factories;

use App\Models\ProfessionalArea;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Topic>
 */
class TopicFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => fake()->sentence(6),
            'description' => fake()->paragraph(3),
            'user_id' => User::inRandomOrder()->first()?->id ?? User::factory(),
            'professional_area_id' => ProfessionalArea::inRandomOrder()->first()?->id ?? ProfessionalArea::factory(),
        ];
    }
}

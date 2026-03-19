<?php

namespace Database\Factories;

use App\Models\TopicSubscription;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TopicSubscription>
 */
class TopicSubscriptionFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = TopicSubscription::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            //
        ];
    }
}

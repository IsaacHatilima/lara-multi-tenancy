<?php

namespace Database\Factories;

use App\Enums\BillingInterval;
use App\Enums\Currency;
use App\Models\Plan;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Plan>
 */
class PlanFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->words(2, true),
            'code' => fake()->unique()->slug(2),
            'description' => fake()->sentence(),
            'price_minor' => fake()->numberBetween(0, 100) * 100,
            'currency' => Currency::Eur,
            'interval' => BillingInterval::Month,
            'trial_days' => 0,
        ];
    }
}

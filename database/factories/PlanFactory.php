<?php

namespace Database\Factories;

use App\Enums\BillingInterval;
use App\Enums\PlanDeployment;
use App\Enums\PlanStatus;
use App\Models\Plan;
use App\Models\Tenant;
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
            'code' => fake()->unique()->slug(2),
            'name' => fake()->words(2, true),
            'status' => PlanStatus::Active,
            'deployment' => PlanDeployment::Hosted,
            'is_public' => true,
            'is_default' => false,
            'price_minor' => fake()->numberBetween(0, 10000),
            'currency' => 'EUR',
            'interval' => BillingInterval::Month,
        ];
    }

    /**
     * Make the plan its deployment's default.
     */
    public function default(): static
    {
        return $this->state(['is_default' => true]);
    }

    /**
     * Make the plan a custom plan for one new company.
     */
    public function custom(): static
    {
        return $this->state([
            'tenant_id' => Tenant::factory(),
            'is_public' => false,
        ]);
    }

    /**
     * Retire the plan, as a price change does.
     */
    public function inactive(): static
    {
        return $this->state(['status' => PlanStatus::Inactive]);
    }
}

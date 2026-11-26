<?php

namespace Database\Factories;

use App\Enums\SubscriptionStatus;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Subscription>
 */
class SubscriptionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'tenant_id' => Tenant::factory(),
            'plan_id' => Plan::factory(),
            'status' => SubscriptionStatus::Active,
        ];
    }

    public function trialing(): static
    {
        return $this->state(['status' => SubscriptionStatus::Trialing]);
    }

    public function active(): static
    {
        return $this->state(['status' => SubscriptionStatus::Active]);
    }

    public function pastDue(): static
    {
        return $this->state(['status' => SubscriptionStatus::PastDue]);
    }

    public function readOnly(): static
    {
        return $this->state(['status' => SubscriptionStatus::ReadOnly]);
    }

    public function canceled(): static
    {
        return $this->state(['status' => SubscriptionStatus::Canceled]);
    }

    public function ended(): static
    {
        return $this->state(['status' => SubscriptionStatus::Ended]);
    }
}

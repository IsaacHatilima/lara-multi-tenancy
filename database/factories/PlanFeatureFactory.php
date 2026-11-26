<?php

namespace Database\Factories;

use App\Models\Feature;
use App\Models\Plan;
use App\Models\PlanFeature;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PlanFeature>
 */
class PlanFeatureFactory extends Factory
{
    /**
     * Define the model's default state: the plan includes the feature without a limit.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'plan_id' => Plan::factory(),
            'feature_id' => Feature::factory(),
            'limit' => null,
        ];
    }

    /**
     * Cap the feature on the plan.
     */
    public function limit(int $limit): static
    {
        return $this->state(['limit' => $limit]);
    }
}

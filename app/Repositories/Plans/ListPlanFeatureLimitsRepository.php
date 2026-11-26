<?php

declare(strict_types=1);

namespace App\Repositories\Plans;

use App\Models\Plan;

final readonly class ListPlanFeatureLimitsRepository
{
    /**
     * The features the plan includes, keyed by feature id, each with its limit or null for unlimited.
     *
     * @return array<int, int|null>
     */
    public function handle(Plan $plan): array
    {
        return $plan->features()->pluck('plan_feature.limit', 'features.id')->all();
    }
}

<?php

declare(strict_types=1);

namespace App\Repositories\Features;

use App\Models\PlanFeature;

final readonly class ListPlanFeatureLimitsRepository
{
    /**
     * The features the plan includes, keyed by feature key, each with its limit or null for unlimited.
     *
     * @return array<string, int|null>
     */
    public function handle(string $planId): array
    {
        return PlanFeature::query()
            ->join('features', 'features.id', '=', 'plan_feature.feature_id')
            ->where('plan_feature.plan_id', $planId)
            ->pluck('plan_feature.limit', 'features.key')
            ->all();
    }
}

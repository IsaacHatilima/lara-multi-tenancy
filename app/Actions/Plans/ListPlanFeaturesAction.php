<?php

declare(strict_types=1);

namespace App\Actions\Plans;

use App\Data\Plans\PlanFeatureResource;
use App\Models\Feature;
use App\Models\Plan;
use App\Repositories\Features\FeatureRepository;
use App\Repositories\Plans\PlanRepository;

final readonly class ListPlanFeaturesAction
{
    public function __construct(
        private FeatureRepository $features,
        private PlanRepository $plans,
    ) {
    }

    /**
     * Every feature, each marked with whether the plan includes it and its limit there.
     *
     * @return list<PlanFeatureResource>
     */
    public function handle(Plan $plan): array
    {
        $planLimits = $this->plans->featureLimits($plan);

        return array_values($this->features->list()
            ->map(fn (Feature $feature): PlanFeatureResource => PlanFeatureResource::fromModel($feature, $planLimits))
            ->all());
    }
}

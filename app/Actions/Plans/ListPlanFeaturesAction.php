<?php

declare(strict_types=1);

namespace App\Actions\Plans;

use App\Data\Plans\PlanFeatureResource;
use App\Models\Feature;
use App\Models\Plan;
use App\Repositories\Features\ListFeaturesRepository;
use App\Repositories\Plans\ListPlanFeatureLimitsRepository;

final readonly class ListPlanFeaturesAction
{
    public function __construct(
        private ListFeaturesRepository $listFeatures,
        private ListPlanFeatureLimitsRepository $listPlanFeatureLimits,
    ) {
    }

    /**
     * Every feature, each marked with whether the plan includes it and its limit there.
     *
     * @return list<PlanFeatureResource>
     */
    public function handle(Plan $plan): array
    {
        $planLimits = $this->listPlanFeatureLimits->handle($plan);

        return array_values($this->listFeatures->handle()
            ->map(fn (Feature $feature): PlanFeatureResource => PlanFeatureResource::fromModel($feature, $planLimits))
            ->all());
    }
}

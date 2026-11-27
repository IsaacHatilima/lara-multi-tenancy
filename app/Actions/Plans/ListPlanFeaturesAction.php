<?php

declare(strict_types=1);

namespace App\Actions\Plans;

use App\Data\Plans\PlanFeatureResource;
use App\Models\Plan;
use App\Models\PlanFeature;
use App\Repositories\Plans\PlanRepository;

final readonly class ListPlanFeaturesAction
{
    public function __construct(
        private PlanRepository $plans,
    ) {
    }

    /**
     * @return list<PlanFeatureResource>
     */
    public function handle(Plan $plan): array
    {
        return array_values($this->plans->features($plan)
            ->map(fn (PlanFeature $planFeature): PlanFeatureResource => PlanFeatureResource::fromModel($planFeature))
            ->all());
    }
}

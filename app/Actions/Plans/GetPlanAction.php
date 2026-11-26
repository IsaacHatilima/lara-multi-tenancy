<?php

declare(strict_types=1);

namespace App\Actions\Plans;

use App\Data\Plans\PlanDetailsResource;
use App\Data\Plans\PlanFeatureResource;
use App\Models\Plan;
use App\Repositories\Billing\LoadPlanTenantRepository;
use App\Repositories\Features\ListFeatureKeysRepository;
use App\Repositories\Features\ListPlanFeatureLimitsRepository;

final readonly class GetPlanAction
{
    public function __construct(
        private LoadPlanTenantRepository $loadTenant,
        private ListFeatureKeysRepository $listFeatureKeys,
        private ListPlanFeatureLimitsRepository $listPlanFeatureLimits,
    ) {
    }

    public function handle(Plan $plan): PlanDetailsResource
    {
        $planLimits = $this->listPlanFeatureLimits->handle($plan->id);

        return PlanDetailsResource::fromModel(
            $this->loadTenant->handle($plan),
            array_map(
                fn (string $key): PlanFeatureResource => PlanFeatureResource::fromKey($key, $planLimits),
                $this->listFeatureKeys->handle(),
            ),
        );
    }
}

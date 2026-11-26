<?php

declare(strict_types=1);

namespace App\Actions\Features;

use App\Repositories\Billing\GetDefaultPlanRepository;
use App\Repositories\Features\ListPlanFeatureLimitsRepository;

/**
 * Whether the hosted default plan includes the feature, so a read-only tenant keeps what that plan offers.
 */
final readonly class IsFeatureOnDefaultPlanAction
{
    public function __construct(
        private GetDefaultPlanRepository $getDefaultPlan,
        private ListPlanFeatureLimitsRepository $listPlanFeatureLimits,
    ) {
    }

    public function handle(string $key): bool
    {
        $plan = $this->getDefaultPlan->handle();

        return $plan !== null && array_key_exists($key, $this->listPlanFeatureLimits->handle($plan->id));
    }
}

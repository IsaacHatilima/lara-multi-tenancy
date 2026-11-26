<?php

declare(strict_types=1);

namespace App\Actions\Features;

use App\Repositories\Billing\GetDefaultPlanRepository;
use App\Repositories\Billing\ListTenantIdsOnPlanRepository;
use App\Repositories\Billing\ListTenantIdsWithoutCurrentSubscriptionRepository;
use App\Repositories\Features\TenantFeatureSetCache;

/**
 * Clears the cached feature sets a plan change affects: its subscribers', and when it is or was the hosted default,
 * every tenant without a current subscription.
 */
final readonly class ClearPlanTenantsFeaturesAction
{
    public function __construct(
        private ListTenantIdsOnPlanRepository $listTenantIdsOnPlan,
        private GetDefaultPlanRepository $getDefaultPlan,
        private ListTenantIdsWithoutCurrentSubscriptionRepository $listTenantIdsWithoutCurrentSubscription,
        private TenantFeatureSetCache $tenantFeatureSets,
    ) {
    }

    public function handle(string $planId, bool $wasDefault): void
    {
        $onDefaultPlan = $wasDefault || $this->getDefaultPlan->handle()?->id === $planId
            ? $this->listTenantIdsWithoutCurrentSubscription->handle()
            : [];

        $this->tenantFeatureSets->forget([...$this->listTenantIdsOnPlan->handle($planId), ...$onDefaultPlan]);
    }
}

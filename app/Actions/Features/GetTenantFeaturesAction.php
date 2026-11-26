<?php

declare(strict_types=1);

namespace App\Actions\Features;

use App\Data\Features\FeatureItem;
use App\Data\Features\FeatureSet;
use App\Data\Features\SubscriptionState;
use App\Enums\Feature;
use App\Enums\SubscriptionStatus;
use App\Models\Tenant;
use App\Repositories\Billing\GetCurrentSubscriptionRepository;
use App\Repositories\Billing\GetDefaultPlanRepository;
use App\Repositories\Features\ListFeatureKeysRepository;
use App\Repositories\Features\ListPlanCodesByFeatureRepository;
use App\Repositories\Features\ListPlanFeatureLimitsRepository;

/**
 * Works out a tenant's features from its current subscription's plan, or from the hosted default plan when it has none.
 * The stored status decides, so a trial past its end date keeps its plan until its status changes.
 */
final readonly class GetTenantFeaturesAction
{
    public function __construct(
        private GetCurrentSubscriptionRepository $getCurrentSubscription,
        private GetDefaultPlanRepository $getDefaultPlan,
        private ListPlanFeatureLimitsRepository $listPlanFeatureLimits,
        private ListPlanCodesByFeatureRepository $listPlanCodesByFeature,
        private ListFeatureKeysRepository $listFeatureKeys,
    ) {
    }

    public function handle(Tenant $tenant): FeatureSet
    {
        $subscription = $this->getCurrentSubscription->handle((string) $tenant->getTenantKey());
        $plan = $subscription === null ? $this->getDefaultPlan->handle() : $subscription->plan;

        return FeatureSet::gated(
            planName: $plan?->name,
            planCode: $plan?->code,
            subscription: $subscription === null
                ? null
                : new SubscriptionState($subscription->status, $subscription->trial_ends_at, $subscription->grace_ends_at),
            readOnly: $subscription?->status === SubscriptionStatus::ReadOnly,
            items: $this->items($plan === null ? [] : $this->listPlanFeatureLimits->handle($plan->id)),
        );
    }

    /**
     * One item for every feature row, so keys the enum no longer declares still answer, with no unit.
     *
     * @param  array<string, int|null>  $limits  the plan's features and their limits
     *
     * @return array<string, FeatureItem>
     */
    private function items(array $limits): array
    {
        $availableOn = $this->listPlanCodesByFeature->handle();
        $items = [];

        foreach ($this->listFeatureKeys->handle() as $key) {
            $included = array_key_exists($key, $limits);

            $items[$key] = new FeatureItem(
                enabled: $included,
                limit: $limits[$key] ?? null,
                unit: Feature::limitUnitOf($key),
                availableOn: $included ? [] : $availableOn[$key] ?? [],
            );
        }

        return $items;
    }
}

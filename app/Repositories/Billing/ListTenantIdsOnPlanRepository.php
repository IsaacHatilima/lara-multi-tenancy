<?php

declare(strict_types=1);

namespace App\Repositories\Billing;

use App\Models\Subscription;

final readonly class ListTenantIdsOnPlanRepository
{
    /**
     * The tenants whose current subscription is on the plan.
     *
     * @return list<string>
     */
    public function handle(string $planId): array
    {
        return array_values(Subscription::query()
            ->current()
            ->where('plan_id', $planId)
            ->get(['tenant_id'])
            ->map(fn (Subscription $subscription): string => $subscription->tenant_id)
            ->all());
    }
}

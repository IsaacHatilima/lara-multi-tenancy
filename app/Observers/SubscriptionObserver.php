<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\Subscription;
use App\Repositories\Features\TenantFeatureSetCache;
use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;

/**
 * A subscription write changes its tenant's plan or state, so its cached feature set is cleared once the write commits.
 */
final readonly class SubscriptionObserver implements ShouldHandleEventsAfterCommit
{
    public function __construct(
        private TenantFeatureSetCache $tenantFeatureSets,
    ) {
    }

    public function created(Subscription $subscription): void
    {
        $this->tenantFeatureSets->forget([$subscription->tenant_id]);
    }

    public function updated(Subscription $subscription): void
    {
        $this->tenantFeatureSets->forget([$subscription->tenant_id]);
    }

    public function deleted(Subscription $subscription): void
    {
        $this->tenantFeatureSets->forget([$subscription->tenant_id]);
    }
}

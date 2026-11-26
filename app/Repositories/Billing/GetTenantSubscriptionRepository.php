<?php

declare(strict_types=1);

namespace App\Repositories\Billing;

use App\Data\Billing\SubscriptionRecord;
use App\Models\Tenant;

final readonly class GetTenantSubscriptionRepository
{
    /**
     * Reads sample data until subscriptions have a table; null will mean the company never subscribed.
     */
    public function handle(Tenant $tenant): ?SubscriptionRecord
    {
        return SampleBillingRecords::subscription($tenant);
    }
}

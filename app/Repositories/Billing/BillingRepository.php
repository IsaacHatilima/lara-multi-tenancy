<?php

declare(strict_types=1);

namespace App\Repositories\Billing;

use App\Data\Billing\PaymentRecord;
use App\Data\Billing\SubscriptionRecord;
use App\Models\Tenant;

/**
 * Entry file for all billing persistence. Each operation lives in its own class in this folder; this class composes
 * them so an action needs one import and one constructor parameter instead of one per operation. The operations read
 * sample data until subscriptions and payments have tables.
 */
final readonly class BillingRepository
{
    public function __construct(
        private GetTenantSubscription $getTenantSubscription,
        private ListRecentTenantPayments $listRecentTenantPayments,
        private SumTenantPaidPayments $sumTenantPaidPayments,
    ) {
    }

    public function subscription(Tenant $tenant): ?SubscriptionRecord
    {
        return $this->getTenantSubscription->handle($tenant);
    }

    /**
     * @return list<PaymentRecord>
     */
    public function recentPayments(Tenant $tenant, int $limit): array
    {
        return $this->listRecentTenantPayments->handle($tenant, $limit);
    }

    public function sumPaidPayments(Tenant $tenant): int
    {
        return $this->sumTenantPaidPayments->handle($tenant);
    }
}

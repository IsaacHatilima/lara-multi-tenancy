<?php

declare(strict_types=1);

namespace App\Repositories\Billing;

use App\Data\Billing\PaymentRecord;
use App\Data\Billing\SubscriptionRecord;
use App\Enums\PaymentStatus;
use App\Models\Tenant;

/**
 * Reads sample data until subscriptions and payments have tables.
 */
final readonly class BillingRepository
{
    /**
     * Null means the company never subscribed.
     */
    public function subscription(Tenant $tenant): ?SubscriptionRecord
    {
        return SampleBillingRecords::subscription($tenant);
    }

    /**
     * A company's most recent payments, newest first.
     *
     * @return list<PaymentRecord>
     */
    public function recentPayments(Tenant $tenant, int $limit): array
    {
        return array_slice(SampleBillingRecords::payments($tenant), 0, $limit);
    }

    /**
     * Everything a company has paid, in minor units.
     */
    public function sumPaidPayments(Tenant $tenant): int
    {
        return array_sum(array_map(
            fn (PaymentRecord $payment): int => $payment->status === PaymentStatus::Paid ? $payment->amountMinor : 0,
            SampleBillingRecords::payments($tenant),
        ));
    }
}

<?php

declare(strict_types=1);

namespace App\Repositories\Billing;

use App\Data\Billing\PaymentRecord;
use App\Enums\PaymentStatus;
use App\Models\Tenant;

final readonly class SumTenantPaidPayments
{
    /**
     * Everything a company has paid, in minor units.
     */
    public function handle(Tenant $tenant): int
    {
        return array_sum(array_map(
            fn (PaymentRecord $payment): int => $payment->status === PaymentStatus::Paid ? $payment->amountMinor : 0,
            SampleBillingRecords::payments($tenant),
        ));
    }
}

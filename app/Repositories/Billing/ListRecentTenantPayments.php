<?php

declare(strict_types=1);

namespace App\Repositories\Billing;

use App\Data\Billing\PaymentRecord;
use App\Models\Tenant;

final readonly class ListRecentTenantPayments
{
    /**
     * A company's most recent payments, newest first.
     *
     * @return list<PaymentRecord>
     */
    public function handle(Tenant $tenant, int $limit): array
    {
        return array_slice(SampleBillingRecords::payments($tenant), 0, $limit);
    }
}

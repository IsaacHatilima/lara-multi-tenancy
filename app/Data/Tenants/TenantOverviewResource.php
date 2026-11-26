<?php

declare(strict_types=1);

namespace App\Data\Tenants;

use App\Data\Billing\PaymentResource;
use App\Data\Billing\SubscriptionResource;
use Spatie\LaravelData\Resource;

/**
 * Everything the tenant details page shows.
 */
final class TenantOverviewResource extends Resource
{
    /**
     * @param  list<PaymentResource>  $payments  Newest first.
     */
    public function __construct(
        public readonly TenantResource $tenant,
        public readonly ?SubscriptionResource $subscription,
        public readonly array $payments,
        public readonly string $totalPaid,
        public readonly TenantComplianceResource $compliance,
    ) {
    }
}

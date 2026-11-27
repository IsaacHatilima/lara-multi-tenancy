<?php

declare(strict_types=1);

namespace App\Actions\Tenants;

use App\Data\Billing\Money;
use App\Data\Billing\PaymentRecord;
use App\Data\Billing\PaymentResource;
use App\Data\Billing\SubscriptionResource;
use App\Data\Tenants\TenantComplianceResource;
use App\Data\Tenants\TenantOverviewResource;
use App\Data\Tenants\TenantResource;
use App\Models\Tenant;
use App\Repositories\Billing\BillingRepository;
use App\Repositories\Billing\SampleBillingRecords;
use App\Repositories\Tenants\TenantRepository;

final readonly class GetTenantOverviewAction
{
    private const int RECENT_PAYMENTS = 12;

    public function __construct(
        private TenantRepository $tenants,
        private BillingRepository $billing,
    ) {
    }

    public function handle(Tenant $tenant): TenantOverviewResource
    {
        $subscription = $this->billing->subscription($tenant);

        return new TenantOverviewResource(
            tenant: TenantResource::fromModel($this->tenants->loadDomains($tenant)),
            subscription: $subscription === null ? null : SubscriptionResource::fromRecord($subscription),
            payments: array_map(
                fn (PaymentRecord $payment): PaymentResource => PaymentResource::fromRecord($payment),
                $this->billing->recentPayments($tenant, self::RECENT_PAYMENTS),
            ),
            totalPaid: Money::format(
                $this->billing->sumPaidPayments($tenant),
                $subscription->currency ?? SampleBillingRecords::CURRENCY,
            ),
            compliance: TenantComplianceResource::fromRecords($tenant, $this->tenants->compliance($tenant)),
        );
    }
}

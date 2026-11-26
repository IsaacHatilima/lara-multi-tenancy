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
use App\Repositories\Billing\GetTenantSubscriptionRepository;
use App\Repositories\Billing\ListTenantPaymentsRepository;
use App\Repositories\Billing\SampleBillingRecords;
use App\Repositories\Billing\SumTenantPaidPaymentsRepository;
use App\Repositories\Tenants\GetTenantComplianceRepository;
use App\Repositories\Tenants\LoadTenantDomainsRepository;

final readonly class GetTenantOverviewAction
{
    private const int RECENT_PAYMENTS = 12;

    public function __construct(
        private LoadTenantDomainsRepository $loadDomains,
        private GetTenantSubscriptionRepository $getSubscription,
        private ListTenantPaymentsRepository $listPayments,
        private SumTenantPaidPaymentsRepository $sumPaidPayments,
        private GetTenantComplianceRepository $getCompliance,
    ) {
    }

    public function handle(Tenant $tenant): TenantOverviewResource
    {
        $subscription = $this->getSubscription->handle($tenant);

        return new TenantOverviewResource(
            tenant: TenantResource::fromModel($this->loadDomains->handle($tenant)),
            subscription: $subscription === null ? null : SubscriptionResource::fromRecord($subscription),
            payments: array_map(
                fn (PaymentRecord $payment): PaymentResource => PaymentResource::fromRecord($payment),
                $this->listPayments->handle($tenant, self::RECENT_PAYMENTS),
            ),
            totalPaid: Money::format(
                $this->sumPaidPayments->handle($tenant),
                $subscription->currency ?? SampleBillingRecords::CURRENCY,
            ),
            compliance: TenantComplianceResource::fromRecords($tenant, $this->getCompliance->handle($tenant)),
        );
    }
}

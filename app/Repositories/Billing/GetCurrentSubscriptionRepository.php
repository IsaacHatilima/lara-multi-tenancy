<?php

declare(strict_types=1);

namespace App\Repositories\Billing;

use App\Models\Subscription;

final readonly class GetCurrentSubscriptionRepository
{
    public function handle(string $tenantId): ?Subscription
    {
        return Subscription::query()
            ->current()
            ->where('tenant_id', $tenantId)
            ->with('plan:id,name,code')
            ->first(['id', 'tenant_id', 'plan_id', 'status', 'trial_ends_at', 'grace_ends_at']);
    }
}

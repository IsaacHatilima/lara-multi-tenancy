<?php

declare(strict_types=1);

namespace App\Repositories\Billing;

use App\Enums\PlanDeployment;
use App\Models\Plan;

final readonly class GetDefaultPlanRepository
{
    /**
     * The hosted default plan, which tenants without a current subscription are on.
     */
    public function handle(): ?Plan
    {
        return Plan::query()
            ->where('is_default', true)
            ->where('deployment', PlanDeployment::Hosted)
            ->first(['id', 'name', 'code']);
    }
}

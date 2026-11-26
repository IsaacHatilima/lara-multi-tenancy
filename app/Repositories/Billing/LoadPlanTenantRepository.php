<?php

declare(strict_types=1);

namespace App\Repositories\Billing;

use App\Models\Plan;

final readonly class LoadPlanTenantRepository
{
    /**
     * Loads the company a custom plan belongs to; a public plan has none.
     */
    public function handle(Plan $plan): Plan
    {
        return $plan->load('tenant:id,name');
    }
}

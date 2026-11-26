<?php

declare(strict_types=1);

namespace App\Repositories\Billing;

use App\Enums\PlanDeployment;
use App\Models\Plan;
use Illuminate\Database\Eloquent\Collection;

final readonly class ListPlansRepository
{
    /**
     * Every hosted plan, public and custom, active and inactive, in display order, with a custom plan's tenant.
     *
     * @return Collection<int, Plan>
     */
    public function handle(): Collection
    {
        return Plan::query()
            ->where('deployment', PlanDeployment::Hosted)
            ->with('tenant:id,name')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->orderBy('id')
            ->get(['id', 'code', 'name', 'status', 'tenant_id', 'price_minor', 'currency', 'interval']);
    }
}

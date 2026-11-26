<?php

declare(strict_types=1);

namespace App\Repositories\Features;

use App\Enums\PlanDeployment;
use App\Enums\PlanStatus;
use App\Models\Feature;
use Illuminate\Database\Eloquent\Collection;

final readonly class ListFeaturesRepository
{
    /**
     * Every feature row by key, with the active hosted plans that include it, public and custom, in the plans list's order.
     *
     * @return Collection<int, Feature>
     */
    public function handle(): Collection
    {
        return Feature::query()
            ->with(['plans' => fn ($query) => $query
                ->where('plans.status', PlanStatus::Active)
                ->where('plans.deployment', PlanDeployment::Hosted)
                ->with('tenant:id,name')
                ->orderBy('plans.sort_order')
                ->orderBy('plans.name')
                ->orderBy('plans.id')
                ->select(['plans.id', 'plans.code', 'plans.name', 'plans.tenant_id'])])
            ->orderBy('key')
            ->get(['id', 'key']);
    }
}

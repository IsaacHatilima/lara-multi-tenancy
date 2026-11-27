<?php

declare(strict_types=1);

namespace App\Repositories\Plans;

use App\Models\Feature;
use App\Models\Plan;
use App\Models\PlanFeature;
use Illuminate\Database\Eloquent\Collection;

final readonly class ListPlanFeatures
{
    /**
     * The plan's features with their limits, by feature name.
     *
     * @return Collection<int, PlanFeature>
     */
    public function handle(Plan $plan): Collection
    {
        return PlanFeature::query()
            ->where('plan_id', $plan->id)
            ->with(['feature' => fn ($query) => $query->select(['id', 'key', 'name', 'limit_unit'])])
            ->orderBy(Feature::query()->select('name')->whereColumn('features.id', 'plan_feature.feature_id'))
            ->get(['id', 'plan_id', 'feature_id', 'limit']);
    }
}

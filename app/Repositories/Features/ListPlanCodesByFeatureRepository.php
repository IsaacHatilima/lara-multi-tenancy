<?php

declare(strict_types=1);

namespace App\Repositories\Features;

use App\Enums\PlanDeployment;
use App\Enums\PlanStatus;
use App\Models\PlanFeature;
use Illuminate\Support\Collection;
use stdClass;

final readonly class ListPlanCodesByFeatureRepository
{
    /**
     * For each feature key, the codes of the public, active, hosted plans that include it, in the order they are offered.
     *
     * @return array<string, list<string>>
     */
    public function handle(): array
    {
        return PlanFeature::query()
            ->join('plans', 'plans.id', '=', 'plan_feature.plan_id')
            ->join('features', 'features.id', '=', 'plan_feature.feature_id')
            ->where('plans.status', PlanStatus::Active)
            ->where('plans.deployment', PlanDeployment::Hosted)
            ->where('plans.is_public', true)
            ->whereNull('plans.tenant_id')
            ->orderBy('plans.sort_order')
            ->orderBy('plans.code')
            ->toBase()
            ->get(['features.key', 'plans.code'])
            ->mapToGroups(fn (stdClass $row): array => [$row->key => $row->code])
            ->map(fn (Collection $codes): array => array_values($codes->all()))
            ->all();
    }
}

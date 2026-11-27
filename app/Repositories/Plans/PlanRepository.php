<?php

declare(strict_types=1);

namespace App\Repositories\Plans;

use App\Data\Plans\AddPlanFeatureData;
use App\Data\Plans\CreatePlanData;
use App\Data\Plans\UpdatePlanData;
use App\Models\Feature;
use App\Models\Plan;
use App\Models\PlanFeature;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Entry file for all Plan persistence. Each operation lives in its own class in this folder; this class composes
 * them so an action needs one import and one constructor parameter instead of one per operation.
 */
final readonly class PlanRepository
{
    public function __construct(
        private CreatePlan $createPlan,
        private UpdatePlan $updatePlan,
        private DeletePlan $deletePlan,
        private ListPlans $listPlans,
        private ListPlanFeatures $listPlanFeatures,
        private PlanIncludesFeature $planIncludesFeature,
        private AddPlanFeature $addPlanFeature,
        private UpdatePlanFeatureLimit $updatePlanFeatureLimit,
        private RemovePlanFeature $removePlanFeature,
    ) {
    }

    public function create(CreatePlanData $data): Plan
    {
        return $this->createPlan->handle($data);
    }

    public function update(Plan $plan, UpdatePlanData $data): Plan
    {
        return $this->updatePlan->handle($plan, $data);
    }

    public function delete(Plan $plan): void
    {
        $this->deletePlan->handle($plan);
    }

    /**
     * @return LengthAwarePaginator<int, Plan>
     */
    public function list(Request $request, int $perPage): LengthAwarePaginator
    {
        return $this->listPlans->handle($request, $perPage);
    }

    /**
     * @return Collection<int, PlanFeature>
     */
    public function features(Plan $plan): Collection
    {
        return $this->listPlanFeatures->handle($plan);
    }

    public function includesFeature(Plan $plan, Feature $feature): bool
    {
        return $this->planIncludesFeature->handle($plan, $feature);
    }

    public function addFeature(Plan $plan, AddPlanFeatureData $data): void
    {
        $this->addPlanFeature->handle($plan, $data);
    }

    public function updateFeatureLimit(Plan $plan, Feature $feature, ?int $limit): void
    {
        $this->updatePlanFeatureLimit->handle($plan, $feature, $limit);
    }

    public function removeFeature(Plan $plan, Feature $feature): void
    {
        $this->removePlanFeature->handle($plan, $feature);
    }
}

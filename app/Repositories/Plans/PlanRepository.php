<?php

declare(strict_types=1);

namespace App\Repositories\Plans;

use App\Data\Plans\CreatePlanData;
use App\Data\Plans\UpdatePlanData;
use App\Models\Plan;
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
        private ListPlanFeatureLimits $listPlanFeatureLimits,
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
     * @return array<int, int|null>
     */
    public function featureLimits(Plan $plan): array
    {
        return $this->listPlanFeatureLimits->handle($plan);
    }
}

<?php

declare(strict_types=1);

namespace App\Actions\Plans;

use App\Data\Plans\UpdatePlanData;
use App\Models\Plan;
use App\Repositories\Plans\PlanRepository;

final readonly class UpdatePlanAction
{
    public function __construct(
        private PlanRepository $plans,
    ) {
    }

    public function handle(Plan $plan, UpdatePlanData $data): Plan
    {
        return $this->plans->update($plan, $data);
    }
}

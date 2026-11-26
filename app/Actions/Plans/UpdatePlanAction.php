<?php

declare(strict_types=1);

namespace App\Actions\Plans;

use App\Data\Plans\UpdatePlanData;
use App\Models\Plan;
use App\Repositories\Plans\UpdatePlanRepository;

final readonly class UpdatePlanAction
{
    public function __construct(
        private UpdatePlanRepository $updatePlan,
    ) {
    }

    public function handle(Plan $plan, UpdatePlanData $data): Plan
    {
        return $this->updatePlan->handle($plan, $data);
    }
}

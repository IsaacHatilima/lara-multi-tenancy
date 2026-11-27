<?php

declare(strict_types=1);

namespace App\Actions\Plans;

use App\Data\Plans\CreatePlanData;
use App\Models\Plan;
use App\Repositories\Plans\PlanRepository;

final readonly class CreatePlanAction
{
    public function __construct(
        private PlanRepository $plans,
    ) {
    }

    public function handle(CreatePlanData $data): Plan
    {
        return $this->plans->create($data);
    }
}

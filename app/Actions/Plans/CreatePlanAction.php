<?php

declare(strict_types=1);

namespace App\Actions\Plans;

use App\Data\Plans\CreatePlanData;
use App\Models\Plan;
use App\Repositories\Plans\CreatePlanRepository;

final readonly class CreatePlanAction
{
    public function __construct(
        private CreatePlanRepository $createPlan,
    ) {
    }

    public function handle(CreatePlanData $data): Plan
    {
        return $this->createPlan->handle($data);
    }
}

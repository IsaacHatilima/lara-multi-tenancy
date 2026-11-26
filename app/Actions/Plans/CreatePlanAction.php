<?php

declare(strict_types=1);

namespace App\Actions\Plans;

use App\Data\Plans\CreatePlanData;
use App\Models\Plan;
use App\Models\User;
use App\Repositories\Billing\CreatePlanRepository;

final readonly class CreatePlanAction
{
    public function __construct(
        private CreatePlanRepository $createPlan,
    ) {
    }

    public function handle(CreatePlanData $data, User $actor): Plan
    {
        return $this->createPlan->handle($data, $actor);
    }
}

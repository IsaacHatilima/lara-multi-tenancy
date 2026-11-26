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

    /**
     * The new plan takes everything but its features from the input; only features and limits come from the plan it starts from.
     */
    public function handle(CreatePlanData $data, User $actor): Plan
    {
        return $this->createPlan->handle($data, $actor);
    }
}

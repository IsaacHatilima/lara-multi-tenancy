<?php

declare(strict_types=1);

namespace App\Actions\Plans;

use App\Models\Plan;
use App\Repositories\Plans\PlanRepository;

final readonly class DeletePlanAction
{
    public function __construct(
        private PlanRepository $plans,
    ) {
    }

    public function handle(Plan $plan): void
    {
        $this->plans->delete($plan);
    }
}

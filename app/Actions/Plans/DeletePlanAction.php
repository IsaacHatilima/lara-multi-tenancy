<?php

declare(strict_types=1);

namespace App\Actions\Plans;

use App\Models\Plan;
use App\Repositories\Plans\DeletePlanRepository;

final readonly class DeletePlanAction
{
    public function __construct(
        private DeletePlanRepository $deletePlan,
    ) {
    }

    public function handle(Plan $plan): void
    {
        $this->deletePlan->handle($plan);
    }
}

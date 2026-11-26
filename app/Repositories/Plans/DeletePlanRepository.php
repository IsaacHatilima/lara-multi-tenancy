<?php

declare(strict_types=1);

namespace App\Repositories\Plans;

use App\Models\Plan;

final readonly class DeletePlanRepository
{
    /**
     * The database removes the plan's feature mappings with it.
     */
    public function handle(Plan $plan): void
    {
        $plan->delete();
    }
}

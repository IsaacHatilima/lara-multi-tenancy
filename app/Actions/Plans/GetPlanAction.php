<?php

declare(strict_types=1);

namespace App\Actions\Plans;

use App\Data\Plans\PlanResource;
use App\Models\Plan;

final readonly class GetPlanAction
{
    public function handle(Plan $plan): PlanResource
    {
        return PlanResource::fromModel($plan);
    }
}

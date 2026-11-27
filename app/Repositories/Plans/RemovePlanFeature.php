<?php

declare(strict_types=1);

namespace App\Repositories\Plans;

use App\Models\Feature;
use App\Models\Plan;

final readonly class RemovePlanFeature
{
    public function handle(Plan $plan, Feature $feature): void
    {
        $plan->features()->detach($feature->id);
    }
}

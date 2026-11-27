<?php

declare(strict_types=1);

namespace App\Repositories\Plans;

use App\Models\Feature;
use App\Models\Plan;

final readonly class PlanIncludesFeature
{
    public function handle(Plan $plan, Feature $feature): bool
    {
        return $plan->features()->whereKey($feature->id)->exists();
    }
}

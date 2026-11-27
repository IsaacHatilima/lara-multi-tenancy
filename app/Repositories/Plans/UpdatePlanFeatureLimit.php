<?php

declare(strict_types=1);

namespace App\Repositories\Plans;

use App\Models\Feature;
use App\Models\Plan;

final readonly class UpdatePlanFeatureLimit
{
    public function handle(Plan $plan, Feature $feature, ?int $limit): void
    {
        $plan->features()->updateExistingPivot($feature->id, ['limit' => $limit]);
    }
}

<?php

declare(strict_types=1);

namespace App\Repositories\Plans;

use App\Data\Plans\AddPlanFeatureData;
use App\Models\Plan;

final readonly class AddPlanFeature
{
    public function handle(Plan $plan, AddPlanFeatureData $data): void
    {
        $plan->features()->attach($data->featureId, ['limit' => $data->limit]);
    }
}

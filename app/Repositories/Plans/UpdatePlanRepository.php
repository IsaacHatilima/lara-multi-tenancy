<?php

declare(strict_types=1);

namespace App\Repositories\Plans;

use App\Data\Plans\UpdatePlanData;
use App\Models\Plan;

final readonly class UpdatePlanRepository
{
    /**
     * Saves the plan's fields and the features it includes together: a feature left out of the data is removed.
     */
    public function handle(Plan $plan, UpdatePlanData $data): Plan
    {
        return $plan->getConnection()->transaction(function () use ($plan, $data): Plan {
            $plan->update([
                'name' => $data->name,
                'code' => $data->code,
                'description' => $data->description,
            ]);

            $plan->features()->sync(array_map(
                fn (?int $limit): array => ['limit' => $limit],
                $data->featureLimits,
            ));

            return $plan;
        });
    }
}

<?php

declare(strict_types=1);

namespace App\Repositories\Plans;

use App\Data\Plans\CreatePlanData;
use App\Data\Plans\UpdatePlanData;
use App\Models\Plan;
use Illuminate\Database\Eloquent\Collection;

final readonly class PlanRepository
{
    public function create(CreatePlanData $data): Plan
    {
        $plan = new Plan;

        $plan->name = $data->name;
        $plan->code = $data->code;
        $plan->description = $data->description;
        $plan->save();

        return $plan;
    }

    /**
     * Saves the plan's fields and the features it includes together: a feature left out of the data is removed.
     */
    public function update(Plan $plan, UpdatePlanData $data): Plan
    {
        return $plan->getConnection()->transaction(function () use ($plan, $data): Plan {
            $plan->name = $data->name;
            $plan->code = $data->code;
            $plan->description = $data->description;
            $plan->save();

            $plan->features()->sync(array_map(
                fn (?int $limit): array => ['limit' => $limit],
                $data->featureLimits,
            ));

            return $plan;
        });
    }

    /**
     * The database removes the plan's feature mappings with it.
     */
    public function delete(Plan $plan): void
    {
        $plan->delete();
    }

    /**
     * @return Collection<int, Plan>
     */
    public function list(): Collection
    {
        return Plan::query()
            ->orderBy('name')
            ->orderBy('code')
            ->get(['id', 'name', 'code', 'description']);
    }

    /**
     * The features the plan includes, keyed by feature id, each with its limit or null for unlimited.
     *
     * @return array<int, int|null>
     */
    public function featureLimits(Plan $plan): array
    {
        return $plan->features()->pluck('plan_feature.limit', 'features.id')->all();
    }
}

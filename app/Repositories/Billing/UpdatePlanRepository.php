<?php

declare(strict_types=1);

namespace App\Repositories\Billing;

use App\Data\Plans\PlanFeatureChanges;
use App\Data\Plans\UpdatePlanData;
use App\Models\Feature;
use App\Models\Plan;
use App\Models\User;
use App\Repositories\Features\ListPlanFeatureLimitsRepository;
use Spatie\Activitylog\Enums\ActivityEvent;

final readonly class UpdatePlanRepository
{
    public function __construct(
        private ListPlanFeatureLimitsRepository $listPlanFeatureLimits,
    ) {
    }

    /**
     * Saves the plan's own fields and the features it includes, all or nothing, and logs what changed as one entry; a save that
     * changes nothing writes nothing. The plan row is locked first, so the changes are worked out from what a concurrent save
     * left. Features are written through the plan's relation, which saves the pivot model, so its observer clears the cached
     * feature sets.
     */
    public function handle(Plan $plan, UpdatePlanData $data, User $actor): Plan
    {
        return Plan::query()->getConnection()->transaction(function () use ($plan, $data, $actor): Plan {
            $plan = Plan::query()->lockForUpdate()->findOrFail($plan->id);
            $plan->fill(['name' => $data->name, 'description' => $data->description, 'sort_order' => $data->sortOrder]);
            $attributes = $plan->getDirty();
            $old = array_intersect_key($plan->getOriginal(), $attributes);
            $features = PlanFeatureChanges::between($this->listPlanFeatureLimits->handle($plan->id), $data->featureLimits);

            if ($attributes === [] && $features->isEmpty()) {
                return $plan;
            }

            if ($attributes !== []) {
                $plan->updated_by = $actor->id;
                $plan->save();
            }

            $this->saveFeatures($plan, $features, $actor);

            activity()
                ->performedOn($plan)
                ->causedBy($actor)
                ->event(ActivityEvent::Updated)
                ->withChanges(['attributes' => $attributes, 'old' => $old])
                ->withProperties(['features' => $features->toLog()])
                ->log('updated');

            return $plan;
        });
    }

    private function saveFeatures(Plan $plan, PlanFeatureChanges $features, User $actor): void
    {
        $featureIds = Feature::query()->whereIn('key', $features->keys())->pluck('id', 'key');

        if ($features->added !== []) {
            $plan->features()->attach(collect($features->added)
                ->mapWithKeys(fn (?int $limit, string $key): array => [$featureIds[$key] => ['limit' => $limit, 'updated_by' => $actor->id]])
                ->all());
        }

        if ($features->removed !== []) {
            $plan->features()->detach($featureIds->only(array_keys($features->removed))->values()->all());
        }

        foreach ($features->relimited as $key => $limit) {
            $plan->features()->updateExistingPivot($featureIds[$key], ['limit' => $limit, 'updated_by' => $actor->id]);
        }
    }
}

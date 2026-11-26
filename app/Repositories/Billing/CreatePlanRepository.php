<?php

declare(strict_types=1);

namespace App\Repositories\Billing;

use App\Data\Plans\CreatePlanData;
use App\Enums\PlanDeployment;
use App\Enums\PlanStatus;
use App\Models\Plan;
use App\Models\PlanFeature;
use App\Models\User;

final readonly class CreatePlanRepository
{
    /**
     * Creates an active hosted plan and copies the starting plan's features and limits, all or nothing. The features are
     * written through the plan's relation, which saves the pivot model, so its observer clears the cached feature sets.
     */
    public function handle(CreatePlanData $data, User $actor): Plan
    {
        return Plan::query()->getConnection()->transaction(function () use ($data, $actor): Plan {
            $plan = new Plan([
                'code' => $data->code,
                'name' => $data->name,
                'description' => $data->description,
                'status' => PlanStatus::Active,
                'deployment' => PlanDeployment::Hosted,
                'is_public' => $data->isPublic,
                'is_default' => false,
                'price_minor' => $data->priceMinor,
                'currency' => $data->currency,
                'interval' => $data->interval,
                'trial_days' => $data->trialDays,
                'sort_order' => $data->sortOrder,
            ]);
            $plan->created_by = $actor->id;
            $plan->updated_by = $actor->id;
            $plan->save();

            if ($data->startFromPlanId !== null) {
                $plan->features()->attach(PlanFeature::query()
                    ->where('plan_id', $data->startFromPlanId)
                    ->get(['feature_id', 'limit'])
                    ->mapWithKeys(fn (PlanFeature $feature): array => [
                        $feature->feature_id => ['limit' => $feature->limit, 'updated_by' => $actor->id],
                    ])
                    ->all());
            }

            return $plan;
        });
    }
}

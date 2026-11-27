<?php

declare(strict_types=1);

namespace App\Repositories\Plans;

use App\Data\Plans\UpdatePlanData;
use App\Models\Plan;

final readonly class UpdatePlan
{
    public function handle(Plan $plan, UpdatePlanData $data): Plan
    {
        $plan->name = $data->name;
        $plan->code = $data->code;
        $plan->description = $data->description;
        $plan->price_minor = $data->priceMinor;
        $plan->currency = $data->currency;
        $plan->interval = $data->interval;
        $plan->trial_days = $data->trialDays;
        $plan->save();

        return $plan;
    }
}

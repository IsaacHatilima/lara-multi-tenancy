<?php

declare(strict_types=1);

namespace App\Repositories\Plans;

use App\Data\Plans\CreatePlanData;
use App\Models\Plan;

final readonly class CreatePlan
{
    public function handle(CreatePlanData $data): Plan
    {
        $plan = new Plan();

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

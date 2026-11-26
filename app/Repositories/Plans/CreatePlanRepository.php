<?php

declare(strict_types=1);

namespace App\Repositories\Plans;

use App\Data\Plans\CreatePlanData;
use App\Models\Plan;

final readonly class CreatePlanRepository
{
    public function handle(CreatePlanData $data): Plan
    {
        return Plan::query()->create([
            'name' => $data->name,
            'code' => $data->code,
            'description' => $data->description,
        ]);
    }
}

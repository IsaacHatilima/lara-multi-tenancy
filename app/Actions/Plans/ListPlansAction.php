<?php

declare(strict_types=1);

namespace App\Actions\Plans;

use App\Data\Plans\PlanResource;
use App\Models\Plan;
use App\Repositories\Plans\PlanRepository;

final readonly class ListPlansAction
{
    public function __construct(
        private PlanRepository $plans,
    ) {
    }

    /**
     * @return list<PlanResource>
     */
    public function handle(): array
    {
        return array_values($this->plans->list()
            ->map(fn (Plan $plan): PlanResource => PlanResource::fromModel($plan))
            ->all());
    }
}

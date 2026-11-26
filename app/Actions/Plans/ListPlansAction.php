<?php

declare(strict_types=1);

namespace App\Actions\Plans;

use App\Data\Plans\PlanResource;
use App\Models\Plan;
use App\Repositories\Billing\ListPlansRepository;

final readonly class ListPlansAction
{
    public function __construct(
        private ListPlansRepository $listPlans,
    ) {
    }

    /**
     * @return list<PlanResource>
     */
    public function handle(): array
    {
        return array_values($this->listPlans->handle()
            ->map(fn (Plan $plan): PlanResource => PlanResource::fromModel($plan))
            ->all());
    }
}

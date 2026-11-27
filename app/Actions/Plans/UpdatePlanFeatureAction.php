<?php

declare(strict_types=1);

namespace App\Actions\Plans;

use App\Data\Plans\UpdatePlanFeatureData;
use App\Models\Feature;
use App\Models\Plan;
use App\Repositories\Plans\PlanRepository;
use Illuminate\Database\Eloquent\ModelNotFoundException;

final readonly class UpdatePlanFeatureAction
{
    public function __construct(
        private PlanRepository $plans,
    ) {
    }

    /**
     * @throws ModelNotFoundException<Feature> when the plan doesn't include the feature
     */
    public function handle(Plan $plan, Feature $feature, UpdatePlanFeatureData $data): void
    {
        if (! $this->plans->includesFeature($plan, $feature)) {
            throw (new ModelNotFoundException())->setModel(Feature::class, [$feature->id]);
        }

        $this->plans->updateFeatureLimit($plan, $feature, $data->limit);
    }
}

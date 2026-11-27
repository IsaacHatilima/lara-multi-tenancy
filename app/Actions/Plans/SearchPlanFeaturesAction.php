<?php

declare(strict_types=1);

namespace App\Actions\Plans;

use App\Data\Features\FeatureResource;
use App\Data\Plans\SearchPlanFeaturesData;
use App\Models\Feature;
use App\Models\Plan;
use App\Repositories\Features\FeatureRepository;

/**
 * Finds features a plan doesn't include yet, for the plan page's add-feature search.
 */
final readonly class SearchPlanFeaturesAction
{
    private const int RESULTS = 8;

    public function __construct(
        private FeatureRepository $features,
    ) {
    }

    /**
     * @return list<FeatureResource>
     */
    public function handle(Plan $plan, SearchPlanFeaturesData $data): array
    {
        return array_values($this->features->notOnPlan($plan, $data->search, self::RESULTS)
            ->map(fn (Feature $feature): FeatureResource => FeatureResource::fromModel($feature))
            ->all());
    }
}

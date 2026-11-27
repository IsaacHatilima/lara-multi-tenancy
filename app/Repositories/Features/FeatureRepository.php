<?php

declare(strict_types=1);

namespace App\Repositories\Features;

use App\Data\Features\CreateFeatureData;
use App\Data\Features\UpdateFeatureData;
use App\Models\Feature;
use App\Models\Plan;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Entry file for all Feature persistence. Each operation lives in its own class in this folder; this class composes
 * them so an action needs one import and one constructor parameter instead of one per operation.
 */
final readonly class FeatureRepository
{
    public function __construct(
        private CreateFeature $createFeature,
        private UpdateFeature $updateFeature,
        private DeleteFeature $deleteFeature,
        private ListFeatures $listFeatures,
        private ListFeaturesNotOnPlan $listFeaturesNotOnPlan,
    ) {
    }

    public function create(CreateFeatureData $data): Feature
    {
        return $this->createFeature->handle($data);
    }

    public function update(Feature $feature, UpdateFeatureData $data): Feature
    {
        return $this->updateFeature->handle($feature, $data);
    }

    public function delete(Feature $feature): void
    {
        $this->deleteFeature->handle($feature);
    }

    /**
     * @return LengthAwarePaginator<int, Feature>
     */
    public function list(Request $request, int $perPage): LengthAwarePaginator
    {
        return $this->listFeatures->handle($request, $perPage);
    }

    /**
     * @return Collection<int, Feature>
     */
    public function notOnPlan(Plan $plan, ?string $search, int $limit): Collection
    {
        return $this->listFeaturesNotOnPlan->handle($plan, $search, $limit);
    }
}

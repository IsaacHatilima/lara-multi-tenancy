<?php

declare(strict_types=1);

namespace App\Repositories\Features;

use App\Data\Features\CreateFeatureData;
use App\Data\Features\UpdateFeatureData;
use App\Models\Feature;
use Illuminate\Database\Eloquent\Collection;

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
     * @return Collection<int, Feature>
     */
    public function list(): Collection
    {
        return $this->listFeatures->handle();
    }
}

<?php

declare(strict_types=1);

namespace App\Repositories\Features;

use App\Data\Features\CreateFeatureData;
use App\Data\Features\UpdateFeatureData;
use App\Models\Feature;
use Illuminate\Database\Eloquent\Collection;

final readonly class FeatureRepository
{
    public function create(CreateFeatureData $data): Feature
    {
        $feature = new Feature();

        $feature->key = $data->key;
        $feature->name = $data->name;
        $feature->description = $data->description;
        $feature->limit_unit = $data->limitUnit;
        $feature->save();

        return $feature;
    }

    public function update(Feature $feature, UpdateFeatureData $data): Feature
    {
        $feature->name = $data->name;
        $feature->description = $data->description;
        $feature->limit_unit = $data->limitUnit;
        $feature->save();

        return $feature;
    }

    /**
     * The database removes the feature from every plan that includes it.
     */
    public function delete(Feature $feature): void
    {
        $feature->delete();
    }

    /**
     * @return Collection<int, Feature>
     */
    public function list(): Collection
    {
        return Feature::query()
            ->orderBy('name')
            ->orderBy('key')
            ->get(['id', 'key', 'name', 'description', 'limit_unit']);
    }
}

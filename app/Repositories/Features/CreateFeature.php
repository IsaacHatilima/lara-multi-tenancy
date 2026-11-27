<?php

declare(strict_types=1);

namespace App\Repositories\Features;

use App\Data\Features\CreateFeatureData;
use App\Models\Feature;

final readonly class CreateFeature
{
    public function handle(CreateFeatureData $data): Feature
    {
        $feature = new Feature();

        $feature->key = $data->key;
        $feature->name = $data->name;
        $feature->description = $data->description;
        $feature->limit_unit = $data->limitUnit;
        $feature->save();

        return $feature;
    }
}

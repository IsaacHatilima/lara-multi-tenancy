<?php

declare(strict_types=1);

namespace App\Repositories\Features;

use App\Data\Features\UpdateFeatureData;
use App\Models\Feature;

final readonly class UpdateFeature
{
    public function handle(Feature $feature, UpdateFeatureData $data): Feature
    {
        $feature->name = $data->name;
        $feature->description = $data->description;
        $feature->limit_unit = $data->limitUnit;
        $feature->save();

        return $feature;
    }
}

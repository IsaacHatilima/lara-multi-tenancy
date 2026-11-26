<?php

declare(strict_types=1);

namespace App\Repositories\Features;

use App\Data\Features\UpdateFeatureData;
use App\Models\Feature;

final readonly class UpdateFeatureRepository
{
    public function handle(Feature $feature, UpdateFeatureData $data): Feature
    {
        $feature->update([
            'name' => $data->name,
            'description' => $data->description,
            'limit_unit' => $data->limitUnit,
        ]);

        return $feature;
    }
}

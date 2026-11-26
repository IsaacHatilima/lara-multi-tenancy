<?php

declare(strict_types=1);

namespace App\Repositories\Features;

use App\Data\Features\CreateFeatureData;
use App\Models\Feature;

final readonly class CreateFeatureRepository
{
    public function handle(CreateFeatureData $data): Feature
    {
        return Feature::query()->create([
            'key' => $data->key,
            'name' => $data->name,
            'description' => $data->description,
            'limit_unit' => $data->limitUnit,
        ]);
    }
}

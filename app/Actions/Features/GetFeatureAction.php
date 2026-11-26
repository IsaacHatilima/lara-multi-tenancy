<?php

declare(strict_types=1);

namespace App\Actions\Features;

use App\Data\Features\FeatureResource;
use App\Models\Feature;

final readonly class GetFeatureAction
{
    public function handle(Feature $feature): FeatureResource
    {
        return FeatureResource::fromModel($feature);
    }
}

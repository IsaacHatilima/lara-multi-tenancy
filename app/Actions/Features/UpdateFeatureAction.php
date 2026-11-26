<?php

declare(strict_types=1);

namespace App\Actions\Features;

use App\Data\Features\UpdateFeatureData;
use App\Models\Feature;
use App\Repositories\Features\UpdateFeatureRepository;

final readonly class UpdateFeatureAction
{
    public function __construct(
        private UpdateFeatureRepository $updateFeature,
    ) {
    }

    public function handle(Feature $feature, UpdateFeatureData $data): Feature
    {
        return $this->updateFeature->handle($feature, $data);
    }
}

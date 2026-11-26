<?php

declare(strict_types=1);

namespace App\Actions\Features;

use App\Data\Features\CreateFeatureData;
use App\Models\Feature;
use App\Repositories\Features\CreateFeatureRepository;

final readonly class CreateFeatureAction
{
    public function __construct(
        private CreateFeatureRepository $createFeature,
    ) {
    }

    public function handle(CreateFeatureData $data): Feature
    {
        return $this->createFeature->handle($data);
    }
}

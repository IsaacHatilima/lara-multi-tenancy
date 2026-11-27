<?php

declare(strict_types=1);

namespace App\Actions\Features;

use App\Data\Features\CreateFeatureData;
use App\Models\Feature;
use App\Repositories\Features\FeatureRepository;

final readonly class CreateFeatureAction
{
    public function __construct(
        private FeatureRepository $features,
    ) {
    }

    public function handle(CreateFeatureData $data): Feature
    {
        return $this->features->create($data);
    }
}

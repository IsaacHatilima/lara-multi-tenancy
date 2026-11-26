<?php

declare(strict_types=1);

namespace App\Actions\Features;

use App\Models\Feature;
use App\Repositories\Features\DeleteFeatureRepository;

final readonly class DeleteFeatureAction
{
    public function __construct(
        private DeleteFeatureRepository $deleteFeature,
    ) {
    }

    public function handle(Feature $feature): void
    {
        $this->deleteFeature->handle($feature);
    }
}

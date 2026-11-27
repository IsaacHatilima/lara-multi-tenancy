<?php

declare(strict_types=1);

namespace App\Actions\Features;

use App\Models\Feature;
use App\Repositories\Features\FeatureRepository;

final readonly class DeleteFeatureAction
{
    public function __construct(
        private FeatureRepository $features,
    ) {
    }

    public function handle(Feature $feature): void
    {
        $this->features->delete($feature);
    }
}

<?php

declare(strict_types=1);

namespace App\Actions\Features;

use App\Data\Features\FeatureResource;
use App\Models\Feature;
use App\Repositories\Features\ListFeaturesRepository;

final readonly class ListFeaturesAction
{
    public function __construct(
        private ListFeaturesRepository $listFeatures,
    ) {
    }

    /**
     * @return list<FeatureResource>
     */
    public function handle(): array
    {
        return array_values($this->listFeatures->handle()
            ->map(fn (Feature $feature): FeatureResource => FeatureResource::fromModel($feature))
            ->all());
    }
}

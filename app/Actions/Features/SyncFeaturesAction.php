<?php

declare(strict_types=1);

namespace App\Actions\Features;

use App\Repositories\Features\CreateFeaturesRepository;
use App\Repositories\Features\ListFeatureKeysRepository;

/**
 * Mirrors the feature keys into the features table without ever deleting a row, so a rolled-back deploy keeps its plan mappings.
 */
final readonly class SyncFeaturesAction
{
    public function __construct(
        private ListFeatureKeysRepository $listFeatureKeys,
        private CreateFeaturesRepository $createFeatures,
    ) {
    }

    /**
     * @param  list<string>  $keys
     *
     * @return list<string> the stored keys that aren't among the given ones
     */
    public function handle(array $keys): array
    {
        $storedKeys = $this->listFeatureKeys->handle();

        $this->createFeatures->handle(array_values(array_diff($keys, $storedKeys)));

        return array_values(array_diff($storedKeys, $keys));
    }
}

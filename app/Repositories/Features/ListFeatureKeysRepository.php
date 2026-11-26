<?php

declare(strict_types=1);

namespace App\Repositories\Features;

use App\Models\Feature;

final readonly class ListFeatureKeysRepository
{
    /**
     * @return list<string>
     */
    public function handle(): array
    {
        return array_values(Feature::query()
            ->get(['key'])
            ->map(fn (Feature $feature): string => $feature->key)
            ->all());
    }
}

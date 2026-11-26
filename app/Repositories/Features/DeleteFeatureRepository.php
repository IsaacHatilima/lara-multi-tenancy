<?php

declare(strict_types=1);

namespace App\Repositories\Features;

use App\Models\Feature;

final readonly class DeleteFeatureRepository
{
    /**
     * The database removes the feature from every plan that includes it.
     */
    public function handle(Feature $feature): void
    {
        $feature->delete();
    }
}

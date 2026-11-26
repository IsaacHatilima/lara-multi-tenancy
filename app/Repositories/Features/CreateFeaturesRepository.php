<?php

declare(strict_types=1);

namespace App\Repositories\Features;

use App\Models\Feature;

final readonly class CreateFeaturesRepository
{
    /**
     * Keys that already have a row are skipped, so a concurrent deploy adding the same key can't fail on the unique index.
     *
     * @param  list<string>  $keys
     */
    public function handle(array $keys): void
    {
        $now = now();

        Feature::query()->insertOrIgnore(array_map(
            fn (string $key): array => ['key' => $key, 'created_at' => $now, 'updated_at' => $now],
            $keys,
        ));
    }
}

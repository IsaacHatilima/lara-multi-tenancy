<?php

declare(strict_types=1);

namespace App\Repositories\Features;

use App\Models\Feature;
use Illuminate\Database\Eloquent\Collection;

final readonly class ListFeaturesRepository
{
    /**
     * @return Collection<int, Feature>
     */
    public function handle(): Collection
    {
        return Feature::query()
            ->orderBy('name')
            ->orderBy('key')
            ->get(['id', 'key', 'name', 'description', 'limit_unit']);
    }
}

<?php

declare(strict_types=1);

namespace App\Repositories\Plans;

use App\Models\Plan;
use Illuminate\Database\Eloquent\Collection;

final readonly class ListPlans
{
    /**
     * @return Collection<int, Plan>
     */
    public function handle(): Collection
    {
        return Plan::query()
            ->orderBy('name')
            ->orderBy('code')
            ->get(['id', 'name', 'code', 'description', 'price_minor', 'currency', 'interval', 'trial_days']);
    }
}

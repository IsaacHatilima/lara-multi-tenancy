<?php

declare(strict_types=1);

namespace App\Repositories\Features;

use App\Models\Feature;
use App\Models\Plan;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Query\Builder;

final readonly class ListFeaturesNotOnPlan
{
    /**
     * Up to `$limit` features the plan doesn't include yet, by name, matching the search against name and key.
     *
     * @return Collection<int, Feature>
     */
    public function handle(Plan $plan, ?string $search, int $limit): Collection
    {
        $query = Feature::query();
        $query->whereNotIn('id', fn (Builder $included) => $included
            ->select('feature_id')
            ->from('plan_feature')
            ->where('plan_id', $plan->id));

        if ($search !== null) {
            $query->where(fn ($matches) => $matches
                ->whereLike('name', '%'.$search.'%')
                ->orWhereLike('key', '%'.$search.'%'));
        }

        return $query
            ->orderBy('name')
            ->orderBy('key')
            ->limit($limit)
            ->get(['id', 'key', 'name', 'description', 'limit_unit']);
    }
}

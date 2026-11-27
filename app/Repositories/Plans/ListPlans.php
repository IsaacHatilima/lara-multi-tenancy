<?php

declare(strict_types=1);

namespace App\Repositories\Plans;

use App\Models\Plan;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

final readonly class ListPlans
{
    /**
     * Plans by name. `filter[search]` matches their name and code.
     *
     * @return LengthAwarePaginator<int, Plan>
     */
    public function handle(Request $request, int $perPage): LengthAwarePaginator
    {
        $plans = Plan::query()
            ->select(['id', 'name', 'code', 'description', 'price_minor', 'currency', 'interval', 'trial_days']);

        return QueryBuilder::for($plans, $request)
            ->allowedFilters(
                AllowedFilter::callback('search', fn (Builder $query, string $search) => $query->where(
                    fn (Builder $matches) => $matches
                        ->whereLike('name', '%'.$search.'%')
                        ->orWhereLike('code', '%'.$search.'%'),
                ))->delimiter(''),
            )
            ->orderBy('name')
            ->orderBy('code')
            ->paginate($perPage)
            ->withQueryString();
    }
}

<?php

declare(strict_types=1);

namespace App\Repositories\Features;

use App\Models\Feature;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

final readonly class ListFeatures
{
    /**
     * Features by name. `filter[search]` matches their name and key.
     *
     * @return LengthAwarePaginator<int, Feature>
     */
    public function handle(Request $request, int $perPage): LengthAwarePaginator
    {
        $features = Feature::query()->select(['id', 'key', 'name', 'description', 'limit_unit']);

        return QueryBuilder::for($features, $request)
            ->allowedFilters(
                AllowedFilter::callback('search', fn (Builder $query, string $search) => $query->where(
                    fn (Builder $matches) => $matches
                        ->whereLike('name', '%'.$search.'%')
                        ->orWhereLike('key', '%'.$search.'%'),
                ))->delimiter(''),
            )
            ->orderBy('name')
            ->orderBy('key')
            ->paginate($perPage)
            ->withQueryString();
    }
}

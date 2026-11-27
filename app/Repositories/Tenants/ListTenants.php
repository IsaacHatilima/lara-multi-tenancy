<?php

declare(strict_types=1);

namespace App\Repositories\Tenants;

use App\Models\Tenant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

final readonly class ListTenants
{
    /**
     * Companies by name. `filter[search]` matches their name, email and domains.
     *
     * @return LengthAwarePaginator<int, Tenant>
     */
    public function handle(Request $request, int $perPage): LengthAwarePaginator
    {
        $tenants = Tenant::query()
            ->with(['domains' => fn ($query) => $query->select(['id', 'tenant_id', 'domain', 'slot'])]);

        return QueryBuilder::for($tenants, $request)
            ->allowedFilters(
                AllowedFilter::callback('search', fn (Builder $query, string $search) => $query->where(
                    fn (Builder $matches) => $matches
                        ->whereLike('name', '%'.$search.'%')
                        ->orWhereLike('email', '%'.$search.'%')
                        ->orWhereHas('domains', fn (Builder $domains) => $domains->whereLike('domain', '%'.$search.'%')),
                ))->delimiter(''),
            )
            ->orderBy('name')
            ->orderBy('id')
            ->paginate($perPage)
            ->withQueryString();
    }
}

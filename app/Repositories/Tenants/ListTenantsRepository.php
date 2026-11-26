<?php

declare(strict_types=1);

namespace App\Repositories\Tenants;

use App\Data\Tenants\ListTenantsData;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;

final readonly class ListTenantsRepository
{
    /**
     * Companies by name, matching the search against their name, email and domains.
     *
     * @return LengthAwarePaginator<int, Tenant>
     */
    public function handle(ListTenantsData $data, int $perPage): LengthAwarePaginator
    {
        return Tenant::query()
            ->with(['domains' => fn ($query) => $query->select(['id', 'tenant_id', 'domain', 'slot'])])
            ->when($data->search !== null, fn (Builder $query) => $query->where(
                fn (Builder $matches) => $matches
                    ->whereLike('name', '%'.$data->search.'%')
                    ->orWhereLike('email', '%'.$data->search.'%')
                    ->orWhereHas('domains', fn (Builder $domains) => $domains->whereLike('domain', '%'.$data->search.'%')),
            ))
            ->orderBy('name')
            ->orderBy('id')
            ->paginate($perPage)
            ->withQueryString();
    }
}

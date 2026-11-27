<?php

declare(strict_types=1);

namespace App\Actions\Tenants;

use App\Data\Tenants\TenantResource;
use App\Http\Requests\Tenants\ListTenantsRequest;
use App\Models\Tenant;
use App\Repositories\Tenants\TenantRepository;
use Illuminate\Pagination\LengthAwarePaginator;

final readonly class ListTenantsAction
{
    private const int PER_PAGE = 15;

    public function __construct(
        private TenantRepository $tenants,
    ) {
    }

    /**
     * @return LengthAwarePaginator<int, TenantResource>
     */
    public function handle(ListTenantsRequest $request): LengthAwarePaginator
    {
        return $this->tenants->list($request, self::PER_PAGE)
            ->through(fn (Tenant $tenant): TenantResource => TenantResource::fromModel($tenant));
    }
}

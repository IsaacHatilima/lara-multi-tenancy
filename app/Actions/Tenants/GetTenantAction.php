<?php

declare(strict_types=1);

namespace App\Actions\Tenants;

use App\Data\Tenants\TenantResource;
use App\Models\Tenant;
use App\Repositories\Tenants\LoadTenantDomainsRepository;

final readonly class GetTenantAction
{
    public function __construct(
        private LoadTenantDomainsRepository $loadDomains,
    ) {
    }

    public function handle(Tenant $tenant): TenantResource
    {
        return TenantResource::fromModel($this->loadDomains->handle($tenant));
    }
}

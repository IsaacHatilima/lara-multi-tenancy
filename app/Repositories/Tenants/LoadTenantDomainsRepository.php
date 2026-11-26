<?php

declare(strict_types=1);

namespace App\Repositories\Tenants;

use App\Models\Tenant;

final readonly class LoadTenantDomainsRepository
{
    public function handle(Tenant $tenant): Tenant
    {
        return $tenant->load(['domains' => fn ($query) => $query->select(['id', 'tenant_id', 'domain', 'slot'])]);
    }
}

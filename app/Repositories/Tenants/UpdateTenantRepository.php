<?php

declare(strict_types=1);

namespace App\Repositories\Tenants;

use App\Data\Tenants\SaveTenantData;
use App\Models\Tenant;

final readonly class UpdateTenantRepository
{
    public function handle(Tenant $tenant, SaveTenantData $data): Tenant
    {
        $tenant->fill(TenantColumns::fromData($data))->save();

        return $tenant;
    }
}

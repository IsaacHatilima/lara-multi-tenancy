<?php

declare(strict_types=1);

namespace App\Repositories\Tenants;

use App\Data\Tenants\SaveTenantData;
use App\Models\Tenant;

final readonly class CreateTenantRepository
{
    /**
     * Creating the tenant also creates, migrates and seeds its database through TenantCreated.
     */
    public function handle(SaveTenantData $data): Tenant
    {
        return Tenant::query()->create(TenantColumns::fromData($data));
    }
}

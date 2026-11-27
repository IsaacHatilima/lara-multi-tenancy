<?php

declare(strict_types=1);

namespace App\Actions\Tenants;

use App\Data\Tenants\SaveTenantData;
use App\Models\Tenant;
use App\Repositories\Tenants\TenantRepository;

final readonly class UpdateTenantAction
{
    public function __construct(
        private TenantRepository $tenants,
    ) {
    }

    public function handle(Tenant $tenant, SaveTenantData $data): Tenant
    {
        return $this->tenants->save($data, $tenant);
    }
}

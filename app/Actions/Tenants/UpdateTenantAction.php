<?php

declare(strict_types=1);

namespace App\Actions\Tenants;

use App\Data\Tenants\SaveTenantData;
use App\Models\Tenant;
use App\Repositories\Tenants\UpdateTenantRepository;

final readonly class UpdateTenantAction
{
    public function __construct(
        private UpdateTenantRepository $updateTenant,
    ) {
    }

    public function handle(Tenant $tenant, SaveTenantData $data): Tenant
    {
        return $this->updateTenant->handle($tenant, $data);
    }
}

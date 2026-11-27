<?php

declare(strict_types=1);

namespace App\Repositories\Tenants;

use App\Data\Tenants\ComplianceRecord;
use App\Data\Tenants\SaveTenantData;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Entry file for all Tenant persistence. Each operation lives in its own class in this folder; this class composes
 * them so an action needs one import and one constructor parameter instead of one per operation.
 */
final readonly class TenantRepository
{
    public function __construct(
        private SaveTenant $saveTenant,
        private ListTenants $listTenants,
        private LoadTenantDomains $loadTenantDomains,
        private GetTenantCompliance $getTenantCompliance,
    ) {
    }

    public function save(SaveTenantData $data, ?Tenant $tenant = null): Tenant
    {
        return $this->saveTenant->handle($data, $tenant);
    }

    /**
     * @return LengthAwarePaginator<int, Tenant>
     */
    public function list(Request $request, int $perPage): LengthAwarePaginator
    {
        return $this->listTenants->handle($request, $perPage);
    }

    public function loadDomains(Tenant $tenant): Tenant
    {
        return $this->loadTenantDomains->handle($tenant);
    }

    public function compliance(Tenant $tenant): ComplianceRecord
    {
        return $this->getTenantCompliance->handle($tenant);
    }
}

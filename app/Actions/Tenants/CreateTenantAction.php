<?php

declare(strict_types=1);

namespace App\Actions\Tenants;

use App\Actions\Domains\CreateDomainAction;
use App\Data\Tenants\CreateTenantData;
use App\Models\Tenant;
use App\Repositories\Domains\DomainHostnameTakenRepository;
use App\Repositories\Tenants\CreateTenantRepository;
use Illuminate\Validation\ValidationException;

final readonly class CreateTenantAction
{
    public function __construct(
        private DomainHostnameTakenRepository $hostnameTaken,
        private CreateTenantRepository $createTenant,
        private CreateDomainAction $createDomain,
    ) {
    }

    /**
     * The domain is checked first: creating the tenant provisions its database, which a taken hostname would leave unreachable.
     */
    public function handle(CreateTenantData $data): Tenant
    {
        if ($this->hostnameTaken->handle($data->primaryDomain->hostname)) {
            throw ValidationException::withMessages([
                'domain' => __('This domain is already assigned to a company.'),
            ]);
        }

        $tenant = $this->createTenant->handle($data->company);
        $this->createDomain->handle($tenant, $data->primaryDomain);

        return $tenant;
    }
}

<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tenants;

use App\Actions\Domains\CreateDomainAction;
use App\Actions\Domains\DeleteDomainAction;
use App\Actions\Domains\UpdateDomainAction;
use App\Data\Domains\SaveDomainData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Domains\SaveDomainRequest;
use App\Models\Domain;
use App\Models\Tenant;
use Illuminate\Http\RedirectResponse;
use InertiaUI\Notifications\Toast;

final class TenantDomainController extends Controller
{
    /**
     * Add a domain to a company.
     */
    public function store(SaveDomainRequest $request, Tenant $tenant, CreateDomainAction $createDomain): RedirectResponse
    {
        $createDomain->handle($tenant, SaveDomainData::fromRequest($request));

        Toast::success('Domain added.')->flash();

        return to_route('tenants.edit', $tenant);
    }

    /**
     * Change one of a company's domains.
     */
    public function update(SaveDomainRequest $request, Tenant $tenant, Domain $domain, UpdateDomainAction $updateDomain): RedirectResponse
    {
        $updateDomain->handle($tenant, $domain, SaveDomainData::fromRequest($request));

        Toast::success('Domain updated.')->flash();

        return to_route('tenants.edit', $tenant);
    }

    /**
     * Remove one of a company's domains.
     */
    public function destroy(Tenant $tenant, Domain $domain, DeleteDomainAction $deleteDomain): RedirectResponse
    {
        $deleteDomain->handle($tenant, $domain);

        Toast::success('Domain removed.')->flash();

        return to_route('tenants.edit', $tenant);
    }
}

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
use Inertia\Inertia;

final class TenantDomainController extends Controller
{
    /**
     * Add a domain to a company.
     */
    public function store(SaveDomainRequest $request, Tenant $tenant, CreateDomainAction $createDomain): RedirectResponse
    {
        $createDomain->handle($tenant, SaveDomainData::fromRequest($request));

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Domain added.')]);

        return to_route('tenants.edit', $tenant);
    }

    /**
     * Change one of a company's domains.
     */
    public function update(SaveDomainRequest $request, Tenant $tenant, Domain $domain, UpdateDomainAction $updateDomain): RedirectResponse
    {
        $updateDomain->handle($tenant, $domain, SaveDomainData::fromRequest($request));

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Domain updated.')]);

        return to_route('tenants.edit', $tenant);
    }

    /**
     * Remove one of a company's domains.
     */
    public function destroy(Tenant $tenant, Domain $domain, DeleteDomainAction $deleteDomain): RedirectResponse
    {
        $deleteDomain->handle($tenant, $domain);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Domain removed.')]);

        return to_route('tenants.edit', $tenant);
    }
}

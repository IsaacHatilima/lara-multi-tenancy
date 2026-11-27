<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tenants;

use App\Actions\Tenants\CreateTenantAction;
use App\Actions\Tenants\GetTenantAction;
use App\Actions\Tenants\GetTenantOverviewAction;
use App\Actions\Tenants\ListTenantsAction;
use App\Actions\Tenants\UpdateTenantAction;
use App\Data\Tenants\CreateTenantData;
use App\Data\Tenants\ListTenantsData;
use App\Data\Tenants\SaveTenantData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Tenants\CreateTenantRequest;
use App\Http\Requests\Tenants\ListTenantsRequest;
use App\Http\Requests\Tenants\UpdateTenantRequest;
use App\Models\Tenant;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;
use InertiaUI\Notifications\Toast;

final class TenantController extends Controller
{
    /**
     * List the companies on this deployment with their domains.
     */
    public function index(ListTenantsRequest $request, ListTenantsAction $listTenants): Response
    {
        return Inertia::render('tenants/index', [
            'tenants' => $listTenants->handle($request),
            'filters' => ['search' => ListTenantsData::fromRequest($request)->search],
        ]);
    }

    /**
     * Show the form for a new company.
     */
    public function create(): Response
    {
        return Inertia::render('tenants/create');
    }

    /**
     * Create a company with its primary domain.
     */
    public function store(CreateTenantRequest $request, CreateTenantAction $createTenant): RedirectResponse
    {
        $tenant = $createTenant->handle(CreateTenantData::fromRequest($request));

        Toast::success('Company created.')->flash();

        return to_route('tenants.edit', $tenant);
    }

    /**
     * Show a company's subscription, payments, legal details and domains.
     */
    public function show(Tenant $tenant, GetTenantOverviewAction $getOverview): Response
    {
        return Inertia::render('tenants/show', [
            'overview' => $getOverview->handle($tenant),
        ]);
    }

    /**
     * Show the form for a company's details and domains.
     */
    public function edit(Tenant $tenant, GetTenantAction $getTenant): Response
    {
        return Inertia::render('tenants/edit', [
            'tenant' => $getTenant->handle($tenant),
        ]);
    }

    /**
     * Update a company's details.
     */
    public function update(UpdateTenantRequest $request, Tenant $tenant, UpdateTenantAction $updateTenant): RedirectResponse
    {
        $updateTenant->handle($tenant, SaveTenantData::fromRequest($request));

        Toast::success('Company updated.')->flash();

        return to_route('tenants.edit', $tenant);
    }
}

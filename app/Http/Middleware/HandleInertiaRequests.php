<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Actions\Tenants\IsTenantManagementAvailableAction;
use App\Enums\CentralPermission;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    public function __construct(
        private readonly IsTenantManagementAvailableAction $isTenantManagementAvailable,
    ) {
    }

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default. The user is shared without relations, because permission checks
     * later in the request load its roles and permissions onto the same model.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'name' => config('app.name'),
            'tenantId' => tenancy()->initialized ? (string) tenant()->getTenantKey() : null,
            'auth' => [
                'user' => $request->user()?->withoutRelations(),
            ],
            'canManageTenants' => $request->user() !== null
                && $this->isTenantManagementAvailable->handle($request->getHost()),
            'canManagePlans' => $request->user() !== null
                && $this->isTenantManagementAvailable->handle($request->getHost())
                && $request->user()->checkPermissionTo(CentralPermission::ManagePlans),
            'sidebarOpen' => ! $request->hasCookie('sidebar_state') || $request->cookie('sidebar_state') === 'true',
        ];
    }
}

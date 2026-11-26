<?php

use App\Http\Middleware\AddFeaturesVersionHeader;
use App\Http\Middleware\EnsureFeatureIsAvailable;
use App\Http\Middleware\EnsureTenantManagementIsAvailable;
use App\Http\Middleware\HandleAppearance;
use App\Http\Middleware\HandleInertiaRequests;
use Illuminate\Contracts\Auth\Middleware\AuthenticatesRequests;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Foundation\Http\HealthCheck;
use Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;
use Stancl\Tenancy\Middleware\ScopeSessions;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        commands: __DIR__.'/../routes/console.php',
        using: function (): void {
            Route::get('/up', HealthCheck::class);

            Route::middleware([
                'web',
                InitializeTenancyByDomain::class,
                'universal',
                ScopeSessions::class,
            ])->group(base_path('routes/web.php'));

            Route::middleware('tenant')->group(base_path('routes/tenant.php'));
        },
    )
    ->withBroadcasting(
        __DIR__.'/../routes/channels.php',
        ['middleware' => [
            'web',
            InitializeTenancyByDomain::class,
            'universal',
            ScopeSessions::class,
            'auth',
        ]],
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->preventRequestsDuringMaintenance(except: ['up']);
        $middleware->encryptCookies(except: ['appearance', 'sidebar_state']);

        // Tenant hosts must get a 404 for tenant management, not a login redirect that reveals it exists.
        $middleware->prependToPriorityList(AuthenticatesRequests::class, EnsureTenantManagementIsAvailable::class);

        $middleware->alias(['feature' => EnsureFeatureIsAvailable::class]);

        $middleware->web(append: [
            HandleAppearance::class,
            AddFeaturesVersionHeader::class,
            HandleInertiaRequests::class,
            AddLinkHeadersForPreloadedAssets::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();

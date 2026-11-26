<?php

use App\Enums\CentralPermission;
use App\Http\Controllers\Features\FeatureController;
use App\Http\Middleware\EnsureTenantManagementIsAvailable;
use Illuminate\Support\Facades\Route;
use Spatie\Permission\Middleware\PermissionMiddleware;

Route::middleware([
    EnsureTenantManagementIsAvailable::class,
    'auth',
    'verified',
    PermissionMiddleware::using(CentralPermission::ManagePlans),
])->group(function () {
    Route::resource('features', FeatureController::class)->except(['show']);
});

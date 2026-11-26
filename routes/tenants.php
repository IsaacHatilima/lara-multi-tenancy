<?php

use App\Http\Controllers\Tenants\TenantController;
use App\Http\Controllers\Tenants\TenantDomainController;
use App\Http\Middleware\EnsureTenantManagementIsAvailable;
use Illuminate\Support\Facades\Route;

Route::middleware([EnsureTenantManagementIsAvailable::class, 'auth', 'verified'])->group(function () {
    Route::resource('tenants', TenantController::class)->only(['index', 'create', 'store', 'show', 'edit', 'update']);

    Route::scopeBindings()->group(function () {
        Route::post('tenants/{tenant}/domains', [TenantDomainController::class, 'store'])->name('tenants.domains.store');
        Route::put('tenants/{tenant}/domains/{domain}', [TenantDomainController::class, 'update'])->name('tenants.domains.update');
        Route::delete('tenants/{tenant}/domains/{domain}', [TenantDomainController::class, 'destroy'])->name('tenants.domains.destroy');
    });
});

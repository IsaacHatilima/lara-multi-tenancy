<?php

use App\Enums\CentralPermission;
use App\Http\Controllers\Features\FeatureController;
use App\Http\Controllers\Plans\PlanController;
use App\Http\Controllers\Plans\PlanFeatureController;
use App\Http\Middleware\EnsureTenantManagementIsAvailable;
use Illuminate\Support\Facades\Route;
use Spatie\Permission\Middleware\PermissionMiddleware;

Route::middleware([
    EnsureTenantManagementIsAvailable::class,
    'auth',
    'verified',
    PermissionMiddleware::using(CentralPermission::ManagePlans),
])->group(function () {
    Route::resource('plans', PlanController::class)->except(['show']);
    Route::get('plans/{plan}/features/search', [PlanFeatureController::class, 'search'])->name('plans.features.search');
    Route::post('plans/{plan}/features', [PlanFeatureController::class, 'store'])->name('plans.features.store');
    Route::patch('plans/{plan}/features/{feature}', [PlanFeatureController::class, 'update'])->name('plans.features.update');
    Route::delete('plans/{plan}/features/{feature}', [PlanFeatureController::class, 'destroy'])->name('plans.features.destroy');
    Route::resource('features', FeatureController::class)->except(['show']);
});

<?php

use App\Models\Plan;
use App\Models\Tenant;
use App\Models\User;
use Spatie\Permission\Models\Permission;

test('production gets the plans and permissions and no demo users or companies', function () {
    config(['mitarbo.managed_deployment' => true]);
    app()->detectEnvironment(fn (): string => 'production');

    $this->artisan('db:seed', ['--force' => true])->assertSuccessful();

    expect(Plan::query()->pluck('code')->all())->toEqualCanonicalizing(['free', 'basic', 'pro'])
        ->and(Permission::query()->pluck('name')->all())->toBe(['plans.manage'])
        ->and(User::query()->count())->toBe(0)
        ->and(Tenant::query()->count())->toBe(0);
});

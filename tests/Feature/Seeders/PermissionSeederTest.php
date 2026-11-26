<?php

use Database\Seeders\PermissionSeeder;
use Spatie\Permission\Models\Permission;

test('a managed deployment gets plans.manage on the web guard once', function () {
    config(['mitarbo.managed_deployment' => true]);

    $this->seed(PermissionSeeder::class);
    $this->seed(PermissionSeeder::class);

    expect(Permission::query()->get(['name', 'guard_name'])->toArray())
        ->toBe([['name' => 'plans.manage', 'guard_name' => 'web']]);
});

test('a self-hosted install gets no permissions', function () {
    config(['mitarbo.managed_deployment' => false]);

    $this->seed(PermissionSeeder::class);

    expect(Permission::query()->count())->toBe(0);
});

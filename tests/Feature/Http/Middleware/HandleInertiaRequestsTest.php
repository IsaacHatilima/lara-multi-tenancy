<?php

use App\Enums\CentralPermission;
use App\Models\Tenant;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;
use Spatie\Permission\Models\Permission;

beforeEach(function () {
    config(['mitarbo.managed_deployment' => true, 'tenancy.bootstrappers' => []]);

    $this->tenant = Tenant::withoutEvents(fn (): Tenant => Tenant::factory()->create(['id' => 'acme']));
    $this->tenant->domains()->create(['domain' => 'acme.example.test']);
    $this->user = User::factory()->create();
});

afterEach(function () {
    tenancy()->end();
});

test('central users holding plans.manage may manage plans', function () {
    $this->user->givePermissionTo(Permission::findOrCreate(CentralPermission::ManagePlans, 'web'));

    $response = $this->actingAs($this->user)->get(config('app.url').'/dashboard');

    $response->assertInertia(fn (Assert $page) => $page
        ->where('canManagePlans', true)
        ->missing('auth.user.permissions')
        ->missing('auth.user.roles'));
});

test('plans may not be managed without the permission, off the central host or on a self-hosted install', function (bool $permitted, bool $onCentralHost, bool $managed) {
    Permission::findOrCreate(CentralPermission::ManagePlans, 'web');
    if ($permitted) {
        $this->user->givePermissionTo(CentralPermission::ManagePlans);
    }
    config(['mitarbo.managed_deployment' => $managed]);

    $response = $this->actingAs($this->user)->get(($onCentralHost ? config('app.url') : 'https://acme.example.test').'/dashboard');

    $response->assertInertia(fn (Assert $page) => $page->where('canManagePlans', false));
})->with([
    'central user without the permission' => [false, true, true],
    'permitted user on a tenant domain' => [true, false, true],
    'permitted user on a self-hosted install' => [true, true, false],
]);

test('plans may not be managed before the permission is seeded', function () {
    $response = $this->actingAs($this->user)->get(config('app.url').'/dashboard');

    $response->assertOk()->assertInertia(fn (Assert $page) => $page->where('canManagePlans', false));
});

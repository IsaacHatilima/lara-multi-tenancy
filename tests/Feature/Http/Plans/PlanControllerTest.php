<?php

use App\Enums\CentralPermission;
use App\Models\Domain;
use App\Models\Plan;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Facades\Event;
use Spatie\Permission\Models\Permission;
use Stancl\Tenancy\Events\TenantCreated;

beforeEach(function () {
    config(['tenancy.bootstrappers' => [], 'mitarbo.managed_deployment' => true]);
    Event::fake([TenantCreated::class]);
    Permission::findOrCreate(CentralPermission::ManagePlans, 'web');
});

afterEach(function () {
    tenancy()->end();
});

dataset('plan routes', [
    'list' => ['get', 'plans.index'],
    'create form' => ['get', 'plans.create'],
]);

function planManager(): User
{
    return User::factory()->create()->givePermissionTo(CentralPermission::ManagePlans);
}

function onTenantHost(string $route): string
{
    Domain::factory()->for(Tenant::factory())->create(['domain' => 'acme.example']);

    return 'https://acme.example'.route($route, absolute: false);
}

test('guests on a tenant domain get not found', function (string $method, string $route) {
    $this->call($method, onTenantHost($route))->assertNotFound();
})->with('plan routes');

test('plan managers on a tenant domain get not found', function (string $method, string $route) {
    $this->actingAs(planManager())->call($method, onTenantHost($route))->assertNotFound();
})->with('plan routes');

test('plan managers on a self-hosted install get not found', function (string $method, string $route) {
    config(['mitarbo.managed_deployment' => false]);

    $this->actingAs(planManager())->call($method, route($route))->assertNotFound();
})->with('plan routes');

test('guests on the central host are sent to log in', function (string $method, string $route) {
    $this->call($method, route($route))->assertRedirect(route('login'));
})->with('plan routes');

test('unverified users are sent to verify their email before the permission is checked', function (string $method, string $route) {
    $this->actingAs(User::factory()->unverified()->create())
        ->call($method, route($route))
        ->assertRedirect(route('verification.notice'));
})->with('plan routes');

test('central users without plans.manage are forbidden', function (string $method, string $route) {
    $this->actingAs(User::factory()->create())->call($method, route($route))->assertForbidden();

    expect(Plan::query()->count())->toBe(0);
})->with('plan routes');

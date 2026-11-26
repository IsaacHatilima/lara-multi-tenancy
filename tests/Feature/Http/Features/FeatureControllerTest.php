<?php

use App\Enums\CentralPermission;
use App\Models\Domain;
use App\Models\Feature;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Facades\Event;
use Inertia\Testing\AssertableInertia as Assert;
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

function featuresUrlOnTenantHost(): string
{
    Domain::factory()->for(Tenant::factory())->create(['domain' => 'acme.example']);

    return 'https://acme.example'.route('features.index', absolute: false);
}

test('the features page answers each visitor as the plans routes do', function (Closure $visit) {
    $visit->call($this);
})->with([
    'guest on a tenant host' => fn () => $this->get(featuresUrlOnTenantHost())->assertNotFound(),
    'plan manager on a tenant host' => fn () => $this->actingAs(User::factory()->create()->givePermissionTo(CentralPermission::ManagePlans))
        ->get(featuresUrlOnTenantHost())
        ->assertNotFound(),
    'plan manager on a self-hosted install' => function () {
        config(['mitarbo.managed_deployment' => false]);

        $this->actingAs(User::factory()->create()->givePermissionTo(CentralPermission::ManagePlans))
            ->get(route('features.index'))
            ->assertNotFound();
    },
    'guest on the central host' => fn () => $this->get(route('features.index'))->assertRedirect(route('login')),
    'unverified user' => fn () => $this->actingAs(User::factory()->unverified()->create())
        ->get(route('features.index'))
        ->assertRedirect(route('verification.notice')),
    'user without plans.manage' => fn () => $this->actingAs(User::factory()->create())->get(route('features.index'))->assertForbidden(),
    'plan manager on the central host' => fn () => $this->actingAs(User::factory()->create()->givePermissionTo(CentralPermission::ManagePlans))
        ->get(route('features.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->component('features/index')),
]);

test('plan managers see every feature row by key with its limit unit', function () {
    Feature::factory()->create(['key' => 'test-storage']);
    Feature::factory()->create(['key' => 'test-reports']);

    $this->actingAs(User::factory()->create()->givePermissionTo(CentralPermission::ManagePlans))
        ->get(route('features.index'))
        ->assertInertia(fn (Assert $page) => $page
            ->component('features/index')
            ->has('features', 2)
            ->where('features.0', ['key' => 'test-reports', 'unitLabel' => null, 'plans' => []])
            ->where('features.1.key', 'test-storage')
            ->where('features.1.unitLabel', null));
});

test('without feature rows the page lists no features', function () {
    $this->actingAs(User::factory()->create()->givePermissionTo(CentralPermission::ManagePlans))
        ->get(route('features.index'))
        ->assertInertia(fn (Assert $page) => $page->component('features/index')->where('features', []));
});

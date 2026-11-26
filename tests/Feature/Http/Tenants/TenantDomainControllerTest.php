<?php

use App\Enums\DomainSlot;
use App\Models\Domain;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Facades\Event;
use Stancl\Tenancy\Events\TenantCreated;

beforeEach(function () {
    config(['tenancy.bootstrappers' => [], 'mitarbo.managed_deployment' => true]);
    Event::fake([TenantCreated::class]);
    $this->actingAs(User::factory()->create());
});

test('a second domain takes the free slot', function () {
    $tenant = Tenant::factory()->create();
    Domain::factory()->for($tenant)->create();

    $this->post(route('tenants.domains.store', $tenant), ['domain' => 'Portal.Acme.Example'])
        ->assertRedirect(route('tenants.edit', $tenant));

    expect($tenant->domains()->where('slot', DomainSlot::Secondary)->sole()->domain)->toBe('portal.acme.example');
});

test('a third domain is rejected', function () {
    $tenant = Tenant::factory()->create();
    Domain::factory()->for($tenant)->bothSlots()->create();

    $this->post(route('tenants.domains.store', $tenant), ['domain' => 'third.acme.example'])
        ->assertSessionHasErrors(['domain' => 'A company may have at most two domains.']);

    expect($tenant->domains()->count())->toBe(2);
});

test('a domain is renamed in its slot', function () {
    $tenant = Tenant::factory()->create();
    $domain = Domain::factory()->for($tenant)->secondary()->create(['domain' => 'old.acme.example']);

    $this->put(route('tenants.domains.update', [$tenant, $domain]), ['domain' => 'new.acme.example'])
        ->assertRedirect(route('tenants.edit', $tenant));

    expect($domain->fresh())->domain->toBe('new.acme.example')->slot->toBe(DomainSlot::Secondary);
});

test('a domain cannot be renamed to one another company uses', function () {
    Domain::factory()->create(['domain' => 'taken.example']);
    $tenant = Tenant::factory()->create();
    $domain = Domain::factory()->for($tenant)->create(['domain' => 'acme.example']);

    $this->put(route('tenants.domains.update', [$tenant, $domain]), ['domain' => 'taken.example'])
        ->assertSessionHasErrors(['domain' => 'This domain is already assigned to a company.']);

    expect($domain->fresh()->domain)->toBe('acme.example');
});

test('one of two domains can be removed', function () {
    $tenant = Tenant::factory()->create();
    [$primary, $secondary] = Domain::factory()->for($tenant)->bothSlots()->create();

    $this->delete(route('tenants.domains.destroy', [$tenant, $primary]))
        ->assertRedirect(route('tenants.edit', $tenant));

    expect($tenant->domains()->pluck('id')->all())->toBe([$secondary->id]);
});

test('a company keeps its last domain', function () {
    $tenant = Tenant::factory()->create();
    $domain = Domain::factory()->for($tenant)->create();

    $this->delete(route('tenants.domains.destroy', [$tenant, $domain]))
        ->assertSessionHasErrors(['domain' => 'A company must keep at least one domain.']);

    expect($domain->fresh())->not->toBeNull();
});

test('another company\'s domain cannot be changed through this company', function () {
    $tenant = Tenant::factory()->create();
    Domain::factory()->for($tenant)->create();
    $other = Domain::factory()->bothSlots()->create(['tenant_id' => Tenant::factory()->create()->id]);

    $this->put(route('tenants.domains.update', [$tenant, $other[0]]), ['domain' => 'stolen.example'])->assertNotFound();
    $this->delete(route('tenants.domains.destroy', [$tenant, $other[0]]))->assertNotFound();

    expect($other[0]->fresh()->domain)->not->toBe('stolen.example');
});

test('domains cannot be managed on a self-hosted deployment', function () {
    config(['mitarbo.managed_deployment' => false]);
    $tenant = Tenant::factory()->create();
    [$domain] = Domain::factory()->for($tenant)->bothSlots()->create();

    $this->post(route('tenants.domains.store', $tenant), ['domain' => 'portal.acme.example'])->assertNotFound();
    $this->delete(route('tenants.domains.destroy', [$tenant, $domain]))->assertNotFound();

    expect($tenant->domains()->count())->toBe(2);
});

<?php

use App\Actions\Domains\CreateDomainAction;
use App\Actions\Domains\UpdateDomainAction;
use App\Data\Domains\DomainResource;
use App\Data\Domains\SaveDomainData;
use App\Enums\DomainSlot;
use App\Http\Requests\Domains\SaveDomainRequest;
use App\Models\Domain;
use App\Models\Tenant;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Inertia\Testing\AssertableInertia as Assert;
use Spatie\Activitylog\Models\Activity;
use Stancl\Tenancy\Bootstrappers\RootUrlBootstrapper;
use Stancl\Tenancy\Events\TenantCreated;
use Stancl\Tenancy\Exceptions\DomainOccupiedByOtherTenantException;

beforeEach(function () {
    config(['tenancy.bootstrappers' => []]);

    Route::post('/_test/tenants/{tenant}/domains', function (SaveDomainRequest $request, Tenant $tenant, CreateDomainAction $createDomain): DomainResource {
        return $createDomain->handle($tenant, SaveDomainData::fromRequest($request));
    })->middleware('web');

    Route::put('/_test/tenants/{tenant}/domains/{domain}', function (SaveDomainRequest $request, Tenant $tenant, Domain $domain, UpdateDomainAction $updateDomain): DomainResource {
        return $updateDomain->handle($tenant, $domain, SaveDomainData::fromRequest($request));
    })->middleware('web')->scopeBindings();
});

function createDomain(Tenant $tenant, string $hostname): DomainResource
{
    return app(CreateDomainAction::class)->handle($tenant, new SaveDomainData($hostname, null));
}

afterEach(function () {
    tenancy()->end();
});

test('both company domains use the custom model and identify the same tenant', function () {
    Event::fake([TenantCreated::class]);
    $tenant = Tenant::factory()->create();
    $primary = Domain::factory()->for($tenant)->create(['domain' => ' Company.Mitarbo.Test. ']);
    $secondary = createDomain($tenant, 'support.customer.test');

    expect($primary->fresh())->toBeInstanceOf(Domain::class)
        ->domain->toBe('company.mitarbo.test')
        ->slot->toBe(DomainSlot::Primary);
    expect($secondary->slot)->toBe('secondary');
    expect($primary->tenant->is($tenant))->toBeTrue();

    foreach (['company.mitarbo.test', 'support.customer.test'] as $host) {
        $this->get('https://'.$host.'/register')->assertOk()
            ->assertInertia(fn (Assert $page) => $page->where('tenantId', $tenant->id));
        tenancy()->end();
    }

    Event::assertDispatchedTimes(TenantCreated::class, 1);
});

test('a third domain is rejected without changing the existing two', function () {
    Event::fake([TenantCreated::class]);
    $tenant = Tenant::factory()->create();
    Domain::factory()->for($tenant)->bothSlots()->create();

    $this->postJson('/_test/tenants/'.$tenant->id.'/domains', ['domain' => 'third.customer.test'])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('domain')
        ->assertJsonPath('errors.domain.0', 'A company may have at most two domains.');

    $this->assertDatabaseCount('domains', 2);
    $this->assertDatabaseMissing('domains', ['domain' => 'third.customer.test']);
    Event::assertDispatchedTimes(TenantCreated::class, 1);
});

test('each company has its own two domain slots', function () {
    Event::fake([TenantCreated::class]);
    $tenants = Tenant::factory()->count(2)->create();

    foreach ($tenants as $tenant) {
        createDomain($tenant, 'first.'.$tenant->id.'.test');
        createDomain($tenant, 'second.'.$tenant->id.'.test');
    }

    $this->assertDatabaseCount('domains', 4);
    foreach ($tenants as $tenant) {
        expect($tenant->domains()->pluck('slot')->all())->toBe([DomainSlot::Primary, DomainSlot::Secondary]);
    }
    Event::assertDispatchedTimes(TenantCreated::class, 2);
});

test('an occupied hostname cannot be claimed by another company after normalization', function () {
    Event::fake([TenantCreated::class]);
    $first = Domain::factory()->create(['domain' => 'support.customer.test']);
    $otherTenant = Tenant::factory()->create();

    expect(fn () => $otherTenant->createDomain(' SUPPORT.Customer.Test. '))
        ->toThrow(DomainOccupiedByOtherTenantException::class);

    $this->assertDatabaseCount('domains', 1);
    $this->assertDatabaseHas('domains', ['domain' => 'support.customer.test', 'tenant_id' => $first->tenant_id]);
    Event::assertDispatchedTimes(TenantCreated::class, 2);
});

test('invalid hostnames are rejected before they are saved', function (string $hostname) {
    Event::fake([TenantCreated::class]);
    $tenant = Tenant::factory()->create();

    $this->postJson('/_test/tenants/'.$tenant->id.'/domains', ['domain' => $hostname])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('domain')
        ->assertJsonPath('errors.domain.0', 'Enter a hostname without a scheme, port, or path.');

    $this->assertDatabaseCount('domains', 0);
    Event::assertDispatchedTimes(TenantCreated::class, 1);
})->with(['https://customer.test', 'customer.test/path', 'customer.test:8080', '*.customer.test', '-invalid.test']);

test('a hostname that is too long is rejected with its own message', function (string $hostname, string $message) {
    Event::fake([TenantCreated::class]);
    $tenant = Tenant::factory()->create();

    $this->postJson('/_test/tenants/'.$tenant->id.'/domains', ['domain' => $hostname])
        ->assertUnprocessable()
        ->assertJsonPath('errors.domain.0', $message);

    $this->assertDatabaseCount('domains', 0);
})->with([
    'over 253 characters' => [implode('.', array_fill(0, 5, str_repeat('a', 60))).'.test', 'The domain may not be longer than 253 characters.'],
    'a part over 63 characters' => [str_repeat('a', 64).'.test', 'Each part of the domain between dots may be at most 63 characters.'],
]);

test('the central application hostname cannot be assigned to a company', function () {
    Event::fake([TenantCreated::class]);
    $tenant = Tenant::factory()->create();

    $this->postJson('/_test/tenants/'.$tenant->id.'/domains', ['domain' => config('tenancy.identification.central_domains')[0]])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('domain')
        ->assertJsonPath('errors.domain.0', 'This domain is reserved.');

    $this->assertDatabaseCount('domains', 0);
    Event::assertDispatchedTimes(TenantCreated::class, 1);
});

test('domains can be renamed at the limit and deleted slots can be reused', function () {
    Event::fake([TenantCreated::class]);
    $tenant = Tenant::factory()->create();
    $primary = Domain::factory()->for($tenant)->create();
    Domain::factory()->for($tenant)->secondary()->create();

    $primary->update(['domain' => 'renamed.customer.test']);

    expect($primary->fresh())->domain->toBe('renamed.customer.test')->slot->toBe(DomainSlot::Primary);

    $primary->delete();
    $replacement = createDomain($tenant, 'replacement.customer.test');

    expect($replacement->slot)->toBe('primary');
    $this->assertDatabaseCount('domains', 2);
    Event::assertDispatchedTimes(TenantCreated::class, 1);
});

test('database constraints prevent bypassing the two domain limit', function (?string $slot) {
    Event::fake([TenantCreated::class]);
    $tenant = Tenant::factory()->create();
    Domain::factory()->for($tenant)->bothSlots()->create();

    expect(fn () => DB::transaction(fn () => DB::table('domains')->insert([
        'tenant_id' => $tenant->id,
        'domain' => 'third.customer.test',
        'slot' => $slot,
    ])))->toThrow(QueryException::class);

    $this->assertDatabaseCount('domains', 2);
    Event::assertDispatchedTimes(TenantCreated::class, 1);
})->with(['primary', 'third', null]);

test('an explicitly invalid or occupied domain slot is rejected without inserting a domain', function (string $slot, string $message) {
    Event::fake([TenantCreated::class]);
    $primary = Domain::factory()->create();

    $this->postJson('/_test/tenants/'.$primary->tenant_id.'/domains', ['domain' => 'second.customer.test', 'slot' => $slot])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('slot')
        ->assertJsonPath('errors.slot.0', $message);

    $this->assertDatabaseCount('domains', 1);
    $this->assertDatabaseMissing('domains', ['domain' => 'second.customer.test']);
    Event::assertDispatchedTimes(TenantCreated::class, 1);
})->with([
    'occupied' => ['primary', 'This domain slot is already occupied for the company.'],
    'invalid' => ['third', 'The selected slot is invalid.'],
]);

test('domain requests normalize hostnames and preserve the route bound company', function () {
    Event::fake([TenantCreated::class]);
    $tenant = Tenant::factory()->create();
    $otherTenant = Tenant::factory()->create();

    $this->postJson('/_test/tenants/'.$tenant->id.'/domains', [
        'domain' => ' Support.Customer.Test. ',
        'tenant_id' => $otherTenant->id,
    ])->assertCreated()->assertJsonPath('domain', 'support.customer.test')->assertJsonPath('slot', 'primary');

    $this->assertDatabaseHas('domains', ['domain' => 'support.customer.test', 'tenant_id' => $tenant->id, 'slot' => 'primary']);
    Event::assertDispatchedTimes(TenantCreated::class, 2);
});

test('domain requests report occupied hostnames as validation errors after normalization', function () {
    Event::fake([TenantCreated::class]);
    $domain = Domain::factory()->create(['domain' => 'support.customer.test']);
    $otherTenant = Tenant::factory()->create();

    $this->postJson('/_test/tenants/'.$otherTenant->id.'/domains', [
        'domain' => ' SUPPORT.Customer.Test. ',
        'id' => $domain->id,
    ])->assertUnprocessable()
        ->assertJsonValidationErrors('domain')
        ->assertJsonPath('errors.domain.0', 'This domain is already assigned to a company.');

    $this->assertDatabaseCount('domains', 1);
    Event::assertDispatchedTimes(TenantCreated::class, 2);
});

test('a domain request can retain or rename the current domain when both slots are occupied', function () {
    Event::fake([TenantCreated::class]);
    $domain = Domain::factory()->create();
    Domain::factory()->secondary()->create(['tenant_id' => $domain->tenant_id]);

    foreach ([$domain->domain, 'renamed.customer.test'] as $hostname) {
        $this->putJson('/_test/tenants/'.$domain->tenant_id.'/domains/'.$domain->id, [
            'domain' => $hostname,
            'slot' => 'primary',
        ])->assertOk()->assertJsonPath('domain', $hostname);
    }

    $this->assertDatabaseHas('domains', ['id' => $domain->id, 'domain' => 'renamed.customer.test', 'slot' => 'primary']);
    $this->assertDatabaseCount('domains', 2);
    Event::assertDispatchedTimes(TenantCreated::class, 1);
});

test('a domain request cannot update a domain bound to another company', function () {
    Event::fake([TenantCreated::class]);
    $domain = Domain::factory()->create();
    $otherTenant = Tenant::factory()->create();

    $this->putJson('/_test/tenants/'.$otherTenant->id.'/domains/'.$domain->id, ['domain' => 'changed.customer.test'])
        ->assertNotFound();

    $this->assertDatabaseHas('domains', ['id' => $domain->id, 'domain' => $domain->domain, 'tenant_id' => $domain->tenant_id]);
    Event::assertDispatchedTimes(TenantCreated::class, 2);
});

test('direct model writes still cannot exceed the database domain limit', function () {
    Event::fake([TenantCreated::class]);
    $tenant = Tenant::factory()->create();
    Domain::factory()->for($tenant)->bothSlots()->create();

    expect(fn () => DB::transaction(fn () => $tenant->createDomain('third.customer.test')))
        ->toThrow(QueryException::class);

    $this->assertDatabaseCount('domains', 2);
    Event::assertDispatchedTimes(TenantCreated::class, 1);
});

test('domain changes are recorded in the activity log', function () {
    Event::fake([TenantCreated::class]);
    $tenant = Tenant::factory()->create();

    createDomain($tenant, 'support.customer.test');

    $activity = Activity::query()->where('subject_type', Domain::class)->sole();

    expect($activity->attribute_changes?->get('attributes'))->toMatchArray([
        'domain' => 'support.customer.test',
        'slot' => 'primary',
        'tenant_id' => $tenant->id,
    ]);
    Event::assertDispatchedTimes(TenantCreated::class, 1);
});

test('generated tenant links prefer the primary domain regardless of creation order', function () {
    Event::fake([TenantCreated::class]);
    config(['tenancy.bootstrappers' => [RootUrlBootstrapper::class]]);
    $tenant = Tenant::factory()->create();
    Domain::factory()->for($tenant)->secondary()->create(['domain' => 'secondary.customer.test']);
    $tenant->load('domains');
    Domain::factory()->for($tenant)->create(['domain' => 'primary.customer.test']);

    tenancy()->initialize($tenant);

    expect(route('dashboard'))->toBe('https://primary.customer.test/dashboard');
    Event::assertDispatchedTimes(TenantCreated::class, 1);
});

test('the domain migration assigns slots to existing domains without changing their ownership', function () {
    Event::fake([TenantCreated::class]);
    $tenant = Tenant::factory()->create();
    $migration = require database_path('migrations/central/2026_11_25_170402_add_slots_to_domains_table.php');
    $migration->down();
    DB::table('domains')->insert([
        ['domain' => 'first.customer.test', 'tenant_id' => $tenant->id],
        ['domain' => 'second.customer.test', 'tenant_id' => $tenant->id],
    ]);

    $migration->up();

    $this->assertDatabaseHas('domains', ['domain' => 'first.customer.test', 'tenant_id' => $tenant->id, 'slot' => 'primary']);
    $this->assertDatabaseHas('domains', ['domain' => 'second.customer.test', 'tenant_id' => $tenant->id, 'slot' => 'secondary']);
    Event::assertDispatchedTimes(TenantCreated::class, 1);
});

test('migration refuses existing tenants with more than two domains without deleting data', function () {
    Event::fake([TenantCreated::class]);
    $tenant = Tenant::factory()->create();
    $migration = require database_path('migrations/central/2026_11_25_170402_add_slots_to_domains_table.php');
    $migration->down();
    DB::table('domains')->insert([
        ['domain' => 'first.customer.test', 'tenant_id' => $tenant->id],
        ['domain' => 'second.customer.test', 'tenant_id' => $tenant->id],
        ['domain' => 'third.customer.test', 'tenant_id' => $tenant->id],
    ]);

    expect(fn () => $migration->up())->toThrow(RuntimeException::class, 'A tenant has more than two domains.');

    $this->assertDatabaseCount('domains', 3);
    expect(Schema::hasColumn('domains', 'slot'))->toBeFalse();
    Event::assertDispatchedTimes(TenantCreated::class, 1);
});

<?php

use App\Enums\DomainSlot;
use App\Models\Domain;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Facades\Event;
use Inertia\Testing\AssertableInertia as Assert;
use Stancl\Tenancy\Events\TenantCreated;

beforeEach(function () {
    config(['tenancy.bootstrappers' => [], 'mitarbo.managed_deployment' => true]);
    Event::fake([TenantCreated::class]);
});

afterEach(function () {
    tenancy()->end();
});

/**
 * @return array<string, string|null>
 */
function companyDetails(array $overrides = []): array
{
    return [
        'name' => 'Acme',
        'legal_name' => null,
        'email' => null,
        'phone' => null,
        'website' => null,
        'registration_number' => null,
        'tax_number' => null,
        'address_line_1' => null,
        'address_line_2' => null,
        'city' => null,
        'state' => null,
        'postal_code' => null,
        'country_code' => null,
        'timezone' => null,
        'locale' => null,
        ...$overrides,
    ];
}

test('guests on the central host are sent to log in', function () {
    $this->get(route('tenants.index'))->assertRedirect(route('login'));
});

test('central users of a managed deployment see every company with its domains', function () {
    $acme = Tenant::factory()->create(['name' => 'Acme']);
    Domain::factory()->for($acme)->create(['domain' => 'acme.mitarbo.test']);
    Domain::factory()->for($acme)->secondary()->create(['domain' => 'portal.acme.example']);
    Tenant::factory()->create(['name' => 'Beta']);

    $this->actingAs(User::factory()->create())
        ->get(route('tenants.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('tenants/index')
            ->where('canManageTenants', true)
            ->has('tenants.data', 2)
            ->where('tenants.data.0.name', 'Acme')
            ->where('tenants.data.0.domains.0.domain', 'acme.mitarbo.test')
            ->where('tenants.data.0.domains.0.slot', 'primary')
            ->where('tenants.data.0.domains.1.domain', 'portal.acme.example')
            ->where('tenants.data.1.name', 'Beta')
            ->where('filters.search', null));
});

test('the company list searches names, emails and domains', function (string $search) {
    $acme = Tenant::factory()->create(['name' => 'Acme', 'email' => 'hello@acme.example']);
    Domain::factory()->for($acme)->create(['domain' => 'portal.acme.example']);
    Tenant::factory()->create(['name' => 'Beta', 'email' => 'hello@beta.example']);

    $this->actingAs(User::factory()->create())
        ->get(route('tenants.index', ['filter' => ['search' => $search]]))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->has('tenants.data', 1)
            ->where('tenants.data.0.name', 'Acme')
            ->where('filters.search', trim($search)));
})->with([
    'name' => ['acm'],
    'email' => ['hello@acme'],
    'domain' => [' portal.acme '],
]);

test('the company list rejects an invalid page', function () {
    $this->actingAs(User::factory()->create())
        ->get(route('tenants.index', ['page' => 0]))
        ->assertSessionHasErrors('page');
});

test('the company list rejects filters it does not offer', function () {
    $this->actingAs(User::factory()->create())
        ->get(route('tenants.index', ['filter' => ['plan' => 'pro']]))
        ->assertSessionHasErrors('filter');
});

test('the company list searches a term with a comma as one value', function () {
    Tenant::factory()->create(['name' => 'Acme, Inc.']);
    Tenant::factory()->create(['name' => 'Inc Holdings']);

    $this->actingAs(User::factory()->create())
        ->get(route('tenants.index', ['filter' => ['search' => 'Acme, Inc']]))
        ->assertInertia(fn (Assert $page) => $page
            ->has('tenants.data', 1)
            ->where('tenants.data.0.name', 'Acme, Inc.'));
});

test('tenant management is hidden on a self-hosted deployment', function () {
    config(['mitarbo.managed_deployment' => false]);
    $tenant = Tenant::factory()->create();

    $this->actingAs(User::factory()->create());

    $this->get(route('tenants.index'))->assertNotFound();
    $this->get(route('tenants.show', $tenant))->assertNotFound();
    $this->get(route('tenants.edit', $tenant))->assertNotFound();
    $this->post(route('tenants.store'), companyDetails(['domain' => 'acme.example']))->assertNotFound();
    $this->get(route('dashboard'))->assertInertia(fn (Assert $page) => $page->where('canManageTenants', false));
    expect(Tenant::count())->toBe(1);
});

test('tenant management is hidden on a tenant host, even from guests', function () {
    $tenant = Tenant::factory()->create();
    Domain::factory()->for($tenant)->create(['domain' => 'acme.example']);

    $this->get('https://acme.example/tenants')->assertNotFound();
    tenancy()->end();

    $this->actingAs(User::factory()->create())
        ->get('https://acme.example/dashboard')
        ->assertInertia(fn (Assert $page) => $page->where('canManageTenants', false));
});

test('a company is created with its primary domain', function () {
    $response = $this->actingAs(User::factory()->create())
        ->post(route('tenants.store'), companyDetails([
            'name' => '  Acme  ',
            'email' => ' Hello@Acme.Example ',
            'country_code' => 'de',
            'city' => '   ',
            'domain' => ' Portal.Acme.Example. ',
        ]));

    $tenant = Tenant::sole();
    $response->assertRedirect(route('tenants.edit', $tenant));
    expect($tenant->only(['name', 'email', 'country_code', 'city']))
        ->toBe(['name' => 'Acme', 'email' => 'hello@acme.example', 'country_code' => 'DE', 'city' => null]);
    expect($tenant->domains()->sole())
        ->domain->toBe('portal.acme.example')
        ->slot->toBe(DomainSlot::Primary);
    Event::assertDispatchedTimes(TenantCreated::class, 1);
});

test('a company is not created when its domain is invalid or taken', function (string $domain, string $message) {
    Domain::factory()->create(['domain' => 'taken.example']);
    Event::fake([TenantCreated::class]);

    $this->actingAs(User::factory()->create())
        ->post(route('tenants.store'), companyDetails(['domain' => $domain]))
        ->assertSessionHasErrors(['domain' => $message]);

    expect(Tenant::count())->toBe(1);
    Event::assertNotDispatched(TenantCreated::class);
})->with([
    'taken' => ['Taken.Example', 'This domain is already assigned to a company.'],
    'central host' => [fn (): string => parse_url(config('app.url'), PHP_URL_HOST), 'This domain is reserved.'],
    'with a scheme' => ['https://acme.example', 'Enter a hostname without a scheme, port, or path.'],
]);

test('a company needs a name and a domain', function () {
    $this->actingAs(User::factory()->create())
        ->post(route('tenants.store'), companyDetails(['name' => ' ']))
        ->assertSessionHasErrors(['name', 'domain']);

    expect(Tenant::count())->toBe(0);
});

test('the edit page shows the company details and domains', function () {
    $tenant = Tenant::factory()->withCompanyDetails()->create(['name' => 'Acme']);
    Domain::factory()->for($tenant)->create(['domain' => 'acme.mitarbo.test']);

    $this->actingAs(User::factory()->create())
        ->get(route('tenants.edit', $tenant))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('tenants/edit')
            ->where('tenant.id', $tenant->id)
            ->where('tenant.name', 'Acme')
            ->where('tenant.email', $tenant->email)
            ->where('tenant.countryCode', $tenant->country_code)
            ->where('tenant.domains.0.domain', 'acme.mitarbo.test')
            ->where('tenant.domains.0.slotLabel', 'Primary'));
});

test('a company\'s details are updated and blank fields are cleared', function () {
    $tenant = Tenant::factory()->withCompanyDetails()->create(['name' => 'Acme']);

    $this->actingAs(User::factory()->create())
        ->put(route('tenants.update', $tenant), companyDetails([
            'name' => 'Acme Group',
            'email' => 'Team@Acme.Example',
            'legal_name' => '',
            'tenancy_db_name' => 'untrusted_database',
        ]))
        ->assertRedirect(route('tenants.edit', $tenant));

    expect($tenant->fresh())
        ->name->toBe('Acme Group')
        ->email->toBe('team@acme.example')
        ->legal_name->toBeNull()
        ->city->toBeNull()
        ->getAttribute('tenancy_db_name')->toBeNull();
});

test('updating a company validates its details', function () {
    $tenant = Tenant::factory()->create(['name' => 'Acme']);

    $this->actingAs(User::factory()->create())
        ->put(route('tenants.update', $tenant), companyDetails(['email' => 'invalid', 'website' => 'ftp://acme.example']))
        ->assertSessionHasErrors(['email', 'website']);

    expect($tenant->fresh()->name)->toBe('Acme');
});

test('the details page shows the company and its legal details', function () {
    $tenant = Tenant::factory()->withCompanyDetails()->create([
        'name' => 'Acme',
        'legal_name' => 'Acme GmbH',
        'tax_number' => 'DE123456789',
        'address_line_1' => 'Example Street 1',
        'address_line_2' => null,
        'postal_code' => '10115',
        'city' => 'Berlin',
        'state' => null,
    ]);
    Domain::factory()->for($tenant)->create(['domain' => 'acme.mitarbo.test']);

    $this->actingAs(User::factory()->create())
        ->get(route('tenants.show', $tenant))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('tenants/show')
            ->where('overview.tenant.name', 'Acme')
            ->where('overview.tenant.domains.0.domain', 'acme.mitarbo.test')
            ->where('overview.compliance.legalName', 'Acme GmbH')
            ->where('overview.compliance.taxNumber', 'DE123456789')
            ->where('overview.compliance.address', ['Example Street 1', '10115 Berlin'])
            ->where('overview.compliance.termsAcceptedAt', $tenant->created_at->toIso8601String()));
});

/*
 * Until billing has tables, SampleBillingRecords derives a company's billing from its id;
 * these ids land on each of its cases.
 */
test('the details page shows the subscription and payments, newest first', function (string $tenantId, string $status, string $latestPayment) {
    $tenant = Tenant::factory()->create(['id' => $tenantId]);

    $this->actingAs(User::factory()->create())
        ->get(route('tenants.show', $tenant))
        ->assertInertia(fn (Assert $page) => $page
            ->where('overview.subscription.status', $status)
            ->where('overview.subscription.paymentMethod', fn (string $method) => str_contains($method, 'ending'))
            ->has('overview.payments', 8)
            ->where('overview.payments.0.status', $latestPayment)
            ->where('overview.payments', function ($payments) {
                $dates = collect($payments)->pluck('attemptedAt')->all();

                return $dates === collect($dates)->sortDesc()->values()->all();
            }));
})->with([
    'active' => ['00000000-0000-4000-8000-000000000008', 'active', 'paid'],
    'past due' => ['00000000-0000-4000-8000-000000000001', 'past_due', 'failed'],
]);

test('only paid payments count towards the total', function () {
    $tenant = Tenant::factory()->create(['id' => '00000000-0000-4000-8000-000000000001']);

    $this->actingAs(User::factory()->create())
        ->get(route('tenants.show', $tenant))
        ->assertInertia(function (Assert $page) {
            $overview = $page->toArray()['props']['overview'];
            $amount = fn (string $formatted): float => (float) preg_replace('/[^0-9.]/', '', $formatted);

            expect($amount($overview['totalPaid']))->toBe(7 * $amount($overview['payments'][1]['amount']));
        });
});

test('a company without a subscription has no payments', function () {
    $tenant = Tenant::factory()->create(['id' => '00000000-0000-4000-8000-000000000007']);

    $this->actingAs(User::factory()->create())
        ->get(route('tenants.show', $tenant))
        ->assertInertia(fn (Assert $page) => $page
            ->where('overview.subscription', null)
            ->has('overview.payments', 0));
});

test('the company list shows 15 companies a page', function () {
    Tenant::factory()->count(16)->sequence(fn ($sequence) => ['name' => sprintf('Company %02d', $sequence->index + 1)])->create();

    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('tenants.index'))
        ->assertInertia(fn (Assert $page) => $page
            ->has('tenants.data', 15)
            ->where('tenants.per_page', 15)
            ->where('tenants.total', 16)
            ->where('tenants.last_page', 2));

    $this->actingAs($user)
        ->get(route('tenants.index', ['page' => 2]))
        ->assertInertia(fn (Assert $page) => $page
            ->has('tenants.data', 1)
            ->where('tenants.data.0.name', 'Company 16'));
});

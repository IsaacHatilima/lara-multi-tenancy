<?php

use App\Enums\DomainSlot;
use App\Http\Middleware\ConfigureTenantPasskeys;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Inertia\Testing\AssertableInertia as Assert;
use Stancl\Tenancy\Bootstrappers\DatabaseTenancyBootstrapper;
use Stancl\Tenancy\Bootstrappers\Integrations\FortifyRouteBootstrapper;
use Stancl\Tenancy\Database\TenantDatabaseManagers\SQLiteDatabaseManager;
use Stancl\Tenancy\Middleware\ScopeSessions;
use Tests\TestCase;

beforeEach(function () {
    $this->tenantDatabasePaths = [];
    $this->originalSqlitePath = SQLiteDatabaseManager::$path;
    SQLiteDatabaseManager::$path = sys_get_temp_dir();

    config([
        'tenancy.bootstrappers' => [
            DatabaseTenancyBootstrapper::class,
            FortifyRouteBootstrapper::class,
        ],
    ]);
});

afterEach(function () {
    tenancy()->end();
    DB::purge('tenant_fixture');

    foreach ($this->tenantDatabasePaths as $path) {
        foreach ([$path, $path.'-wal', $path.'-shm', $path.'-journal'] as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
    }

    SQLiteDatabaseManager::$path = $this->originalSqlitePath;
});

test('registration uses the tenant identified by the request domain', function () {
    createAuthenticationTenant($this);

    $response = $this->get('https://auth-tenant.example.test/register');

    $response->assertOk()->assertInertia(fn (Assert $page) => $page
        ->component('auth/register')
        ->where('tenantId', 'auth-tenant'));
});

test('tenant registration creates an account only in the tenant database and requires email verification', function () {
    createAuthenticationTenant($this);
    User::factory()->create(['email' => 'member@example.com']);
    Notification::fake();

    $response = $this->post('https://auth-tenant.example.test/register', [
        'name' => 'Tenant Member',
        'email' => 'member@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $response->assertSessionHasNoErrors()
        ->assertRedirect('https://auth-tenant.example.test/dashboard');
    $this->assertAuthenticated();
    $this->assertDatabaseHas('users', [
        'name' => 'Tenant Member',
        'email' => 'member@example.com',
    ], 'tenant_fixture');
    $this->assertDatabaseMissing('users', ['name' => 'Tenant Member'], 'sqlite');
    $this->assertDatabaseCount('users', 1, 'sqlite');

    $user = User::on('tenant_fixture')->where('email', 'member@example.com')->firstOrFail();
    Notification::assertSentTo($user, VerifyEmail::class);

    $this->get($response->headers->get('Location'))
        ->assertRedirect('https://auth-tenant.example.test/email/verify');
});

test('new tenant accounts ignore a previous accounts intended verification URL', function () {
    createAuthenticationTenant($this);
    Notification::fake();

    $response = $this->withSession([
        'url.intended' => 'https://auth-tenant.example.test/email/verify/1/old-verification-link',
    ])->post('https://auth-tenant.example.test/register', [
        'name' => 'New Tenant Member',
        'email' => 'new-member@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $response->assertSessionHasNoErrors()
        ->assertRedirect('https://auth-tenant.example.test/dashboard')
        ->assertSessionMissing('url.intended');
    $this->assertAuthenticated();
    $this->assertDatabaseHas('users', ['email' => 'new-member@example.com'], 'tenant_fixture');
    $this->assertDatabaseMissing('users', ['email' => 'new-member@example.com'], 'sqlite');
    Notification::assertSentTo(User::on('tenant_fixture')->firstOrFail(), VerifyEmail::class);
});

test('tenant login rejects credentials belonging only to the central database', function () {
    createAuthenticationTenant($this);
    $user = User::factory()->create();

    $response = $this->post('https://auth-tenant.example.test/login', [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $response->assertSessionHasErrors('email');
    $this->assertGuest();
});

test('tenant login redirects to the tenant dashboard', function () {
    $tenant = createAuthenticationTenant($this);
    tenancy()->initialize($tenant);
    $user = User::factory()->create();
    tenancy()->end();

    $response = $this->post('https://auth-tenant.example.test/login', [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $response->assertSessionHasNoErrors()
        ->assertRedirect('https://auth-tenant.example.test/dashboard');
    $this->assertAuthenticatedAs($user);
});

test('tenant registration returns 403 for a session from another tenant', function () {
    createAuthenticationTenant($this);

    $response = $this->withSession([ScopeSessions::$tenantIdKey => 'other-tenant'])
        ->post('https://auth-tenant.example.test/register', [
            'name' => 'Tenant Member',
            'email' => 'member@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

    $response->assertForbidden();
    $this->assertDatabaseCount('users', 0, 'tenant_fixture');
    $this->assertDatabaseCount('users', 0, 'sqlite');
});

test('tenant dashboard redirects guests to login', function () {
    createAuthenticationTenant($this);

    $response = $this->get('https://auth-tenant.example.test/dashboard');

    $response->assertRedirect('https://auth-tenant.example.test/login');
});

test('authenticated tenants can open their dashboard and profile on a custom domain', function () {
    $tenant = createAuthenticationTenant($this);
    $tenant->domains()->create(['domain' => 'support.customer.test', 'slot' => DomainSlot::Secondary]);
    tenancy()->initialize($tenant);
    $user = User::factory()->create();
    tenancy()->end();

    $this->actingAs($user)->get('https://support.customer.test/dashboard')
        ->assertOk()->assertInertia(fn (Assert $page) => $page
        ->component('dashboard')
        ->where('tenantId', 'auth-tenant'));

    $this->get('https://support.customer.test/settings/profile')
        ->assertOk()->assertInertia(fn (Assert $page) => $page
        ->component('settings/profile')
        ->where('auth.user.email', $user->email));

    expect(route('dashboard'))->toBe('https://support.customer.test/dashboard');
    expect(route('profile.edit'))->toBe('https://support.customer.test/settings/profile');
});

test('tenant passkey login options use the current registered domain', function (string $host) {
    $tenant = createAuthenticationTenant($this);
    $tenant->domains()->create(['domain' => 'support.customer.test', 'slot' => DomainSlot::Secondary]);
    $centralPasskeyConfig = config('passkeys');

    $this->getJson('https://'.$host.'/passkeys/login/options')
        ->assertOk()
        ->assertJsonPath('options.rpId', $host);

    expect(config('passkeys'))->toBe($centralPasskeyConfig);
})->with(['auth-tenant.example.test', 'support.customer.test']);

test('central passkey login options retain the central relying party', function () {
    $this->getJson(config('app.url').'/passkeys/login/options')
        ->assertOk()
        ->assertJsonPath('options.rpId', parse_url(config('app.url'), PHP_URL_HOST));
});

test('tenant passkeys accept only their current origin and restore configuration after errors', function () {
    $tenant = createAuthenticationTenant($this);
    $tenant->domains()->create(['domain' => 'support.customer.test', 'slot' => DomainSlot::Secondary]);
    tenancy()->initialize($tenant);
    $originalConfig = config('passkeys');
    $request = Request::create('https://support.customer.test/passkeys/login/options');

    expect(fn () => app(ConfigureTenantPasskeys::class)->handle($request, function () {
        expect(config('passkeys.allowed_origins'))->toBe(['https://support.customer.test']);

        throw new RuntimeException('Passkey request failed');
    }))->toThrow(RuntimeException::class, 'Passkey request failed');

    expect(config('passkeys'))->toBe($originalConfig);
});

function createAuthenticationTenant(TestCase $testCase): Tenant
{
    $path = tempnam(sys_get_temp_dir(), 'mitarbo-auth-');
    $testCase->tenantDatabasePaths[] = $path;

    config(['database.connections.tenant_fixture' => [
        ...config('database.connections.sqlite'),
        'database' => $path,
    ]]);

    $testCase->artisan('migrate', [
        '--database' => 'tenant_fixture',
        '--path' => ['database/migrations', 'database/migrations/tenant'],
        '--force' => true,
        '--no-interaction' => true,
    ])->assertSuccessful();

    $tenant = Tenant::withoutEvents(fn (): Tenant => Tenant::factory()->create([
        'id' => 'auth-tenant',
        'tenancy_db_name' => basename($path),
    ]));
    $tenant->domains()->create(['domain' => 'auth-tenant.example.test']);

    return $tenant;
}

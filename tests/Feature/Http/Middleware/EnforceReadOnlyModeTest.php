<?php

use App\Models\Plan;
use App\Models\Subscription;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Route;
use Stancl\Tenancy\Bootstrappers\BroadcastChannelPrefixBootstrapper;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;
use Stancl\Tenancy\Middleware\ScopeSessions;

beforeEach(function () {
    config(['mitarbo.managed_deployment' => true, 'tenancy.bootstrappers' => []]);

    Route::middleware(['web', InitializeTenancyByDomain::class, 'universal', ScopeSessions::class])->group(function (): void {
        Route::match(['GET', 'POST', 'PUT', 'PATCH', 'DELETE'], 'test-write', fn (): string => 'reached');
        Route::post('test-unlisted', fn (): string => 'reached')->name('test.unlisted');
        Route::post('test-exempt', fn (): string => 'reached')->name('test.exempt');
    });
    Route::getRoutes()->refreshNameLookups();

    $this->plan = Plan::factory()->create();
    $this->tenant = Tenant::withoutEvents(fn (): Tenant => Tenant::factory()->create(['id' => 'acme']));
    $this->tenant->domains()->create(['domain' => 'acme.example.test']);
    $this->subscribe = fn (string $state): Subscription => Subscription::factory()
        ->{$state}()
        ->create(['tenant_id' => $this->tenant->id, 'plan_id' => $this->plan->id]);
});

afterEach(function () {
    tenancy()->end();
});

test('writes to unnamed routes are refused', function (string $method) {
    ($this->subscribe)('readOnly');

    $response = $this->json($method, 'https://acme.example.test/test-write');

    $response->assertForbidden()->assertExactJson([
        'code' => 'feature_unavailable',
        'feature' => null,
        'message' => 'This account is read-only because its payment is overdue. You can still view and delete existing data.',
    ]);
})->with(['POST', 'PUT', 'PATCH']);

test('writes to named routes that are not exempt are refused', function () {
    ($this->subscribe)('readOnly');

    $response = $this->postJson('https://acme.example.test/test-unlisted');

    $response->assertForbidden()->assertJsonPath('code', 'feature_unavailable');
});

test('reads and deletes pass', function (string $method) {
    ($this->subscribe)('readOnly');

    $response = $this->call($method, 'https://acme.example.test/test-write');

    $response->assertOk();
})->with(['GET', 'HEAD', 'DELETE']);

test('writes to exempt routes pass', function () {
    ($this->subscribe)('readOnly');
    config(['billing.read_only_exempt' => [...config('billing.read_only_exempt'), 'test.exempt']]);

    $response = $this->postJson('https://acme.example.test/test-exempt');

    $response->assertOk();
});

test('the shipped exempt routes are never refused', function (string $name, bool $signedIn) {
    ($this->subscribe)('readOnly');
    $route = Route::getRoutes()->getByName($name);

    if ($signedIn) {
        $this->actingAs(User::factory()->create());
    }

    $response = $this->json($route->methods()[0], 'https://acme.example.test/'.$route->uri());

    expect($response->getContent())->not->toContain('feature_unavailable');
})->with([
    'login.store' => ['login.store', false],
    'logout' => ['logout', true],
    'password.email' => ['password.email', false],
    'password.update' => ['password.update', false],
    'verification.send' => ['verification.send', true],
    'password.confirm.store' => ['password.confirm.store', true],
    'two-factor.login.store' => ['two-factor.login.store', false],
    'passkey.login' => ['passkey.login', false],
    'passkey.confirm' => ['passkey.confirm', true],
    'register.store' => ['register.store', false],
    'user-password.update' => ['user-password.update', true],
    'two-factor.enable' => ['two-factor.enable', true],
    'two-factor.confirm' => ['two-factor.confirm', true],
    'two-factor.regenerate-recovery-codes' => ['two-factor.regenerate-recovery-codes', true],
    'passkey.store' => ['passkey.store', true],
]);

test('a read-only tenant can sign in', function () {
    ($this->subscribe)('readOnly');
    $user = User::factory()->create();

    $response = $this->post('https://acme.example.test/login', ['email' => $user->email, 'password' => 'password']);

    $response->assertRedirect('https://acme.example.test/dashboard');
    $this->assertAuthenticatedAs($user);
});

test('a read-only tenant cannot update a profile', function () {
    ($this->subscribe)('readOnly');
    $user = User::factory()->create(['name' => 'Original Name']);
    $message = 'This account is read-only because its payment is overdue. You can still view and delete existing data.';

    $response = $this->actingAs($user)
        ->from('https://acme.example.test/settings/profile')
        ->withHeaders(['X-Inertia' => 'true'])
        ->patch('https://acme.example.test/settings/profile', ['name' => 'Changed Name', 'email' => $user->email]);

    $response->assertRedirect('https://acme.example.test/settings/profile')
        ->assertSessionHasErrors(['feature_unavailable' => $message])
        ->assertInertiaFlash('toast', ['type' => 'error', 'message' => $message]);
    expect($user->refresh()->name)->toBe('Original Name');
});

test('a read-only tenant can close an account', function () {
    ($this->subscribe)('readOnly');
    $user = User::factory()->create();

    $this->actingAs($user)->delete('https://acme.example.test/settings/profile', ['password' => 'password']);

    expect($user->fresh())->toBeNull();
});

test('a read-only tenant keeps channel authorisation', function () {
    ($this->subscribe)('readOnly');
    config([
        'broadcasting.default' => 'reverb',
        'broadcasting.connections.reverb.key' => 'test-key',
        'broadcasting.connections.reverb.secret' => 'test-secret',
        'broadcasting.connections.reverb.app_id' => 'test-app',
        'tenancy.bootstrappers' => [BroadcastChannelPrefixBootstrapper::class],
    ]);
    Broadcast::purge('reverb');
    require base_path('routes/channels.php');
    $user = User::factory()->create();

    $response = $this->actingAs($user)->postJson('https://acme.example.test/broadcasting/auth', [
        'socket_id' => '1234.5678',
        'channel_name' => 'private-acme.App.Models.User.'.$user->id,
    ]);

    $response->assertOk()->assertJsonStructure(['auth']);
});

test('tenants that are not read-only can write', function (?string $state) {
    if ($state !== null) {
        ($this->subscribe)($state);
    }

    $response = $this->postJson('https://acme.example.test/test-write');

    $response->assertOk();
})->with([
    'active' => 'active',
    'past due' => 'pastDue',
    'no subscription' => null,
]);

test('an ungated host is never read-only', function (bool $managed, string $host) {
    ($this->subscribe)('readOnly');
    config(['mitarbo.managed_deployment' => $managed]);

    $response = $this->postJson($host.'/test-write');

    $response->assertOk();
})->with([
    'managed central host' => fn (): array => [true, config('app.url')],
    'self-hosted install' => [false, 'https://acme.example.test'],
]);

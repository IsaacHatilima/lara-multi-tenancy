<?php

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Facades\Broadcast;
use Inertia\Testing\AssertableInertia as Assert;
use Stancl\Tenancy\Bootstrappers\BroadcastChannelPrefixBootstrapper;
use Stancl\Tenancy\Middleware\ScopeSessions;

beforeEach(function () {
    config([
        'broadcasting.default' => 'reverb',
        'broadcasting.connections.reverb.key' => 'test-key',
        'broadcasting.connections.reverb.secret' => 'test-secret',
        'broadcasting.connections.reverb.app_id' => 'test-app',
        'tenancy.bootstrappers' => [BroadcastChannelPrefixBootstrapper::class],
    ]);

    Broadcast::purge('reverb');

    require base_path('routes/channels.php');
});

afterEach(function () {
    tenancy()->end();
});

test('broadcast authentication returns 401 for guests', function () {
    $response = $this->postJson(config('app.url').'/broadcasting/auth', [
        'socket_id' => '1234.5678',
        'channel_name' => 'private-App.Models.User.1',
    ]);

    $response->assertUnauthorized();
});

test('central users can authorize their own private channel', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)
        ->postJson(config('app.url').'/broadcasting/auth', [
            'socket_id' => '1234.5678',
            'channel_name' => 'private-App.Models.User.'.$user->id,
        ]);

    $response->assertOk()->assertJsonStructure(['auth']);
});

test('central users receive 403 for another users private channel', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();

    $response = $this->actingAs($user)
        ->postJson(config('app.url').'/broadcasting/auth', [
            'socket_id' => '1234.5678',
            'channel_name' => 'private-App.Models.User.'.$otherUser->id,
        ]);

    $response->assertForbidden();
});

test('tenant users can authorize their own tenant private channel', function () {
    createBroadcastTenant('tenant-a');
    $user = User::factory()->create();

    $response = $this->actingAs($user)
        ->postJson('https://tenant-a.example.test/broadcasting/auth', [
            'socket_id' => '1234.5678',
            'channel_name' => 'private-tenant-a.App.Models.User.'.$user->id,
        ]);

    $response->assertOk()->assertJsonStructure(['auth']);
});

test('tenant users receive 403 for another users channel in their tenant', function () {
    createBroadcastTenant('tenant-a');
    $user = User::factory()->create();
    $otherUser = User::factory()->create();

    $response = $this->actingAs($user)
        ->postJson('https://tenant-a.example.test/broadcasting/auth', [
            'socket_id' => '1234.5678',
            'channel_name' => 'private-tenant-a.App.Models.User.'.$otherUser->id,
        ]);

    $response->assertForbidden();
});

test('tenant users receive 403 for channels outside their tenant with the same user id', function (string $channelPrefix) {
    createBroadcastTenant('tenant-a');
    createBroadcastTenant('tenant-b');
    $user = User::factory()->create();

    $response = $this->actingAs($user)
        ->postJson('https://tenant-a.example.test/broadcasting/auth', [
            'socket_id' => '1234.5678',
            'channel_name' => $channelPrefix.$user->id,
        ]);

    $response->assertForbidden();
})->with([
    'another tenant' => ['private-tenant-b.App.Models.User.'],
    'central context' => ['private-App.Models.User.'],
]);

test('central users receive 403 for a tenant channel with the same user id', function () {
    createBroadcastTenant('tenant-a');
    $user = User::factory()->create();

    $response = $this->actingAs($user)
        ->postJson(config('app.url').'/broadcasting/auth', [
            'socket_id' => '1234.5678',
            'channel_name' => 'private-tenant-a.App.Models.User.'.$user->id,
        ]);

    $response->assertForbidden();
});

test('broadcast authentication returns 403 for a session from another tenant', function () {
    createBroadcastTenant('tenant-a');
    createBroadcastTenant('tenant-b');
    $user = User::factory()->create();

    $response = $this->actingAs($user)
        ->withSession([ScopeSessions::$tenantIdKey => 'tenant-b'])
        ->postJson('https://tenant-a.example.test/broadcasting/auth', [
            'socket_id' => '1234.5678',
            'channel_name' => 'private-tenant-a.App.Models.User.'.$user->id,
        ]);

    $response->assertForbidden();
});

test('central pages share a null tenant id for Echo subscriptions', function () {
    $response = $this->get(config('app.url'));

    $response->assertOk()->assertInertia(fn (Assert $page) => $page
        ->where('tenantId', null));
});

test('tenant pages share the current tenant id for Echo subscriptions', function () {
    createBroadcastTenant('tenant-a');
    $user = User::factory()->create();

    $response = $this->actingAs($user)
        ->get('https://tenant-a.example.test/dashboard');

    $response->assertOk()->assertInertia(fn (Assert $page) => $page
        ->where('tenantId', 'tenant-a'));
});

function createBroadcastTenant(string $id): Tenant
{
    $tenant = Tenant::withoutEvents(fn (): Tenant => Tenant::factory()->create(['id' => $id]));
    $tenant->domains()->create(['domain' => $id.'.example.test']);

    return $tenant;
}

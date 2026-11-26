<?php

use App\Enums\CentralPermission;
use App\Models\Feature;
use App\Models\Plan;
use App\Models\PlanFeature;
use App\Models\Subscription;
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

test('tenant users get their plan, subscription and items', function () {
    $basic = Plan::factory()->create(['name' => 'Basic', 'code' => 'basic']);
    $pro = Plan::factory()->create(['code' => 'pro']);
    PlanFeature::factory()->limit(5)->create(['plan_id' => $basic->id, 'feature_id' => Feature::factory()->create(['key' => 'test-reports'])->id]);
    PlanFeature::factory()->create(['plan_id' => $pro->id, 'feature_id' => Feature::factory()->create(['key' => 'test-exports'])->id]);
    $subscription = Subscription::factory()->trialing()->create([
        'tenant_id' => $this->tenant->id,
        'plan_id' => $basic->id,
        'trial_ends_at' => now()->addDays(14),
    ]);

    $response = $this->actingAs($this->user)->get('https://acme.example.test/dashboard');

    $response->assertInertia(fn (Assert $page) => $page
        ->has('features', 6)
        ->where('features.gated', true)
        ->where('features.plan', ['name' => 'Basic', 'code' => 'basic'])
        ->where('features.subscription', [
            'status' => 'trialing',
            'trialEndsAt' => $subscription->trial_ends_at->toIso8601String(),
            'graceEndsAt' => null,
        ])
        ->where('features.readOnly', false)
        ->where('features.items.test-reports', ['enabled' => true, 'limit' => 5, 'unit' => null, 'availableOn' => []])
        ->where('features.items.test-exports', ['enabled' => false, 'limit' => null, 'unit' => null, 'availableOn' => ['pro']])
        ->has('features.version'));
});

test('users of a read-only tenant are told so', function () {
    Subscription::factory()->readOnly()->create(['tenant_id' => $this->tenant->id]);

    $response = $this->actingAs($this->user)->get('https://acme.example.test/dashboard');

    $response->assertInertia(fn (Assert $page) => $page->where('features.readOnly', true));
});

test('a tenant without a subscription gets the default plan', function () {
    Plan::factory()->default()->create(['name' => 'Free', 'code' => 'free']);

    $response = $this->actingAs($this->user)->get('https://acme.example.test/dashboard');

    $response->assertInertia(fn (Assert $page) => $page
        ->where('features.plan', ['name' => 'Free', 'code' => 'free'])
        ->where('features.subscription', null));
});

test('a tenant without a subscription or default plan has no plan', function () {
    $response = $this->actingAs($this->user)->get('https://acme.example.test/dashboard');

    $response->assertInertia(fn (Assert $page) => $page->where('features.plan', null));
});

test('central users get the ungated set', function () {
    $response = $this->actingAs($this->user)->get(config('app.url').'/dashboard');

    $response->assertInertia(fn (Assert $page) => $page
        ->where('features.gated', false)
        ->where('features.plan', null)
        ->where('features.subscription', null)
        ->where('features.readOnly', false));
});

test('guests get no features', function () {
    $response = $this->get('https://acme.example.test/login');

    $response->assertInertia(fn (Assert $page) => $page->missing('features'));
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

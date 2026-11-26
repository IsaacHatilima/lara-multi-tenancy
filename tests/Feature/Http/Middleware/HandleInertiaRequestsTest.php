<?php

use App\Models\Feature;
use App\Models\Plan;
use App\Models\PlanFeature;
use App\Models\Subscription;
use App\Models\Tenant;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

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

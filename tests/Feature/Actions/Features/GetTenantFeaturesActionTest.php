<?php

use App\Actions\Features\GetTenantFeaturesAction;
use App\Enums\SubscriptionStatus;
use App\Models\Feature;
use App\Models\Plan;
use App\Models\PlanFeature;
use App\Models\Subscription;
use App\Models\Tenant;
use Illuminate\Support\Facades\Event;
use Stancl\Tenancy\Events\TenantCreated;

beforeEach(function () {
    Event::fake([TenantCreated::class]);

    $this->features = collect(['test-messaging', 'test-attachments', 'test-reports', 'test-video'])
        ->mapWithKeys(fn (string $key) => [$key => Feature::factory()->create(['key' => $key])]);
    $this->free = Plan::factory()->default()->create(['name' => 'Free', 'code' => 'free', 'sort_order' => 0]);
    $this->basic = Plan::factory()->create(['name' => 'Basic', 'code' => 'basic', 'sort_order' => 1]);
    $this->pro = Plan::factory()->create(['name' => 'Pro', 'code' => 'pro', 'sort_order' => 2]);
    $this->include = function (Plan $plan, string $key, ?int $limit = null): void {
        PlanFeature::factory()->create(['plan_id' => $plan->id, 'feature_id' => $this->features[$key]->id, 'limit' => $limit]);
    };
    ($this->include)($this->free, 'test-messaging', 1);
    ($this->include)($this->basic, 'test-messaging');
    ($this->include)($this->basic, 'test-attachments', 15);
    ($this->include)($this->pro, 'test-video');
    ($this->include)($this->basic, 'test-video');
    $this->tenant = Tenant::factory()->create();
    $this->subscribe = fn (string $state, Plan $plan, array $attributes = []): Subscription => Subscription::factory()
        ->{$state}()
        ->create(['tenant_id' => $this->tenant->id, 'plan_id' => $plan->id, ...$attributes]);
});

test('a tenant without a subscription gets the default plan', function () {
    $set = app(GetTenantFeaturesAction::class)->handle($this->tenant);

    expect($set->gated)->toBeTrue()
        ->and($set->planName)->toBe('Free')
        ->and($set->planCode)->toBe('free')
        ->and($set->subscription)->toBeNull()
        ->and($set->readOnly)->toBeFalse()
        ->and($set->isEnabled('test-messaging'))->toBeTrue()
        ->and($set->isEnabled('test-attachments'))->toBeFalse();
});

test('a current subscription carries its plan and state', function (string $state, SubscriptionStatus $status) {
    ($this->subscribe)($state, $this->basic, ['trial_ends_at' => '2026-12-01 10:00:00', 'grace_ends_at' => '2026-12-08 10:00:00']);

    $set = app(GetTenantFeaturesAction::class)->handle($this->tenant);

    expect($set->planName)->toBe('Basic')
        ->and($set->planCode)->toBe('basic')
        ->and($set->subscription?->status)->toBe($status)
        ->and($set->subscription?->trialEndsAt?->toDateTimeString())->toBe('2026-12-01 10:00:00')
        ->and($set->subscription?->graceEndsAt?->toDateTimeString())->toBe('2026-12-08 10:00:00')
        ->and($set->isEnabled('test-attachments'))->toBeTrue();
})->with([
    'trialing' => ['trialing', SubscriptionStatus::Trialing],
    'active' => ['active', SubscriptionStatus::Active],
    'past due' => ['pastDue', SubscriptionStatus::PastDue],
    'read-only' => ['readOnly', SubscriptionStatus::ReadOnly],
]);

test('a read-only subscription is read-only and keeps its plan features', function () {
    ($this->subscribe)('readOnly', $this->basic);

    $set = app(GetTenantFeaturesAction::class)->handle($this->tenant);

    expect($set->readOnly)->toBeTrue()
        ->and($set->isEnabled('test-attachments'))->toBeTrue()
        ->and($set->limit('test-attachments'))->toBe(15);
});

test('a past due subscription keeps its plan features and is not read-only', function () {
    ($this->subscribe)('pastDue', $this->basic);

    $set = app(GetTenantFeaturesAction::class)->handle($this->tenant);

    expect($set->readOnly)->toBeFalse()
        ->and($set->isEnabled('test-attachments'))->toBeTrue();
});

test('a canceled or ended subscription falls back to the default plan', function (string $state) {
    ($this->subscribe)($state, $this->basic);

    $set = app(GetTenantFeaturesAction::class)->handle($this->tenant);

    expect($set->planCode)->toBe('free')
        ->and($set->subscription)->toBeNull()
        ->and($set->isEnabled('test-attachments'))->toBeFalse();
})->with(['canceled', 'ended']);

test('a trial past its end date keeps its plan while its status is trialing', function () {
    ($this->subscribe)('trialing', $this->basic, ['trial_ends_at' => now()->subDay()]);

    $set = app(GetTenantFeaturesAction::class)->handle($this->tenant);

    expect($set->planCode)->toBe('basic')
        ->and($set->isEnabled('test-attachments'))->toBeTrue();
});

test('a subscription on a retired plan keeps that version of the plan', function () {
    $retired = Plan::factory()->inactive()->create(['name' => 'Pro 2025', 'code' => 'pro']);
    ($this->include)($retired, 'test-reports');
    ($this->subscribe)('active', $retired);

    $set = app(GetTenantFeaturesAction::class)->handle($this->tenant);

    expect($set->planName)->toBe('Pro 2025')
        ->and($set->isEnabled('test-reports'))->toBeTrue()
        ->and($set->isEnabled('test-video'))->toBeFalse();
});

test('a subscription on the tenant custom plan gets that plan features', function () {
    $custom = Plan::factory()->custom()->create(['tenant_id' => $this->tenant->id, 'name' => 'Acme', 'code' => 'acme']);
    ($this->include)($custom, 'test-reports', 3);
    ($this->subscribe)('active', $custom);

    $set = app(GetTenantFeaturesAction::class)->handle($this->tenant);

    expect($set->planCode)->toBe('acme')
        ->and($set->limit('test-reports'))->toBe(3)
        ->and($set->isEnabled('test-messaging'))->toBeFalse();
});

test('an included feature carries its limit, or none when unlimited', function () {
    ($this->subscribe)('active', $this->basic);

    $items = app(GetTenantFeaturesAction::class)->handle($this->tenant)->items;

    expect($items['test-attachments']->enabled)->toBeTrue()
        ->and($items['test-attachments']->limit)->toBe(15)
        ->and($items['test-messaging']->enabled)->toBeTrue()
        ->and($items['test-messaging']->limit)->toBeNull();
});

test('a locked feature lists the offered plans that include it, in order', function () {
    $custom = Plan::factory()->custom()->create(['code' => 'globex']);
    ($this->include)($custom, 'test-video');

    $video = app(GetTenantFeaturesAction::class)->handle($this->tenant)->items['test-video'];

    expect($video->enabled)->toBeFalse()
        ->and($video->limit)->toBeNull()
        ->and($video->availableOn)->toBe(['basic', 'pro']);
});

test('every feature row has an item, and keys the enum does not declare have no unit', function () {
    $items = app(GetTenantFeaturesAction::class)->handle($this->tenant)->items;

    expect(array_keys($items))->toEqualCanonicalizing(['test-messaging', 'test-attachments', 'test-reports', 'test-video'])
        ->and(array_map(fn ($item) => $item->unit, $items))->each->toBeNull();
});

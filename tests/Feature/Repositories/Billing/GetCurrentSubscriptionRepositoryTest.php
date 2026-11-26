<?php

use App\Models\Plan;
use App\Models\Subscription;
use App\Models\Tenant;
use App\Repositories\Billing\GetCurrentSubscriptionRepository;
use Illuminate\Support\Facades\Event;
use Stancl\Tenancy\Events\TenantCreated;

beforeEach(function () {
    Event::fake([TenantCreated::class]);
});

test('the current subscription is returned with its plan name and code', function (string $state) {
    $tenant = Tenant::factory()->create();
    $plan = Plan::factory()->create(['name' => 'Basic', 'code' => 'basic']);
    $subscription = Subscription::factory()->{$state}()->create(['tenant_id' => $tenant->id, 'plan_id' => $plan->id]);
    Subscription::factory()->ended()->create(['tenant_id' => $tenant->id]);

    $current = app(GetCurrentSubscriptionRepository::class)->handle($tenant->id);

    expect($current?->id)->toBe($subscription->id)
        ->and($current->status)->toBe($subscription->status)
        ->and($current->relationLoaded('plan'))->toBeTrue()
        ->and($current->plan->only(['id', 'name', 'code']))->toBe(['id' => $plan->id, 'name' => 'Basic', 'code' => 'basic']);
})->with([
    'trialing' => 'trialing',
    'active' => 'active',
    'past due' => 'pastDue',
    'read-only' => 'readOnly',
]);

test('canceled and ended subscriptions are not current', function () {
    $tenant = Tenant::factory()->create();
    Subscription::factory()->canceled()->create(['tenant_id' => $tenant->id]);
    Subscription::factory()->ended()->create(['tenant_id' => $tenant->id]);

    expect(app(GetCurrentSubscriptionRepository::class)->handle($tenant->id))->toBeNull();
});

test('another tenant current subscription is never returned', function () {
    $tenant = Tenant::factory()->create();
    Subscription::factory()->active()->create();

    expect(app(GetCurrentSubscriptionRepository::class)->handle($tenant->id))->toBeNull();
});

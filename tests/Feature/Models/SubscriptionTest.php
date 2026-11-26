<?php

use App\Enums\SubscriptionStatus;
use App\Models\Subscription;
use App\Models\Tenant;
use Carbon\CarbonImmutable;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Stancl\Tenancy\Events\TenantCreated;

beforeEach(function () {
    Event::fake([TenantCreated::class]);
});

dataset('current statuses', [
    'trialing' => 'trialing',
    'active' => 'active',
    'past due' => 'pastDue',
    'read-only' => 'readOnly',
]);

dataset('statuses', [
    'trialing' => ['trialing', 'trialing', SubscriptionStatus::Trialing],
    'active' => ['active', 'active', SubscriptionStatus::Active],
    'past due' => ['pastDue', 'past_due', SubscriptionStatus::PastDue],
    'read-only' => ['readOnly', 'read_only', SubscriptionStatus::ReadOnly],
    'canceled' => ['canceled', 'canceled', SubscriptionStatus::Canceled],
    'ended' => ['ended', 'ended', SubscriptionStatus::Ended],
]);

test('a tenant has only one current subscription', function (string $state, string $secondState) {
    $tenant = Tenant::factory()->create();
    Subscription::factory()->{$state}()->create(['tenant_id' => $tenant->id]);

    expect(fn () => Subscription::factory()->{$secondState}()->create(['tenant_id' => $tenant->id]))
        ->toThrow(UniqueConstraintViolationException::class);
})->with('current statuses')->with('current statuses');

test('canceled and ended subscriptions are kept as history beside the current one', function () {
    $tenant = Tenant::factory()->create();
    Subscription::factory()->active()->create(['tenant_id' => $tenant->id]);
    Subscription::factory()->canceled()->count(2)->create(['tenant_id' => $tenant->id]);
    Subscription::factory()->ended()->count(2)->create(['tenant_id' => $tenant->id]);

    expect($tenant->subscriptions()->count())->toBe(5);
});

test('a subscription stores its status as the raw value and reads it back cast', function (string $state, string $raw, SubscriptionStatus $status) {
    $subscription = Subscription::factory()->{$state}()->create();

    expect(DB::table('subscriptions')->where('id', $subscription->id)->value('status'))->toBe($raw)
        ->and($subscription->fresh()->status)->toBe($status);
})->with('statuses');

test('a subscription reads its dates back as dates and empty ones as null', function () {
    $subscription = Subscription::factory()->create([
        'trial_ends_at' => '2026-12-01 10:00:00',
        'current_period_ends_at' => '2026-12-31 10:00:00',
        'grace_ends_at' => '2027-01-07 10:00:00',
    ])->fresh();

    expect($subscription->trial_ends_at)->toBeInstanceOf(CarbonImmutable::class)
        ->and($subscription->trial_ends_at->toDateTimeString())->toBe('2026-12-01 10:00:00')
        ->and($subscription->current_period_ends_at?->toDateTimeString())->toBe('2026-12-31 10:00:00')
        ->and($subscription->grace_ends_at?->toDateTimeString())->toBe('2027-01-07 10:00:00')
        ->and($subscription->canceled_at)->toBeNull()
        ->and($subscription->ends_at)->toBeNull();

    $final = Subscription::factory()->ended()->create([
        'canceled_at' => '2026-12-15 10:00:00',
        'ends_at' => '2026-12-31 10:00:00',
    ])->fresh();

    expect($final->canceled_at?->toDateTimeString())->toBe('2026-12-15 10:00:00')
        ->and($final->ends_at?->toDateTimeString())->toBe('2026-12-31 10:00:00')
        ->and($final->trial_ends_at)->toBeNull();
});

test('a tenant returns its own current and final subscriptions only', function () {
    $tenant = Tenant::factory()->create();
    $current = Subscription::factory()->active()->create(['tenant_id' => $tenant->id]);
    $final = Subscription::factory()->ended()->create(['tenant_id' => $tenant->id]);
    Subscription::factory()->active()->create();

    expect($tenant->subscriptions->modelKeys())->toEqualCanonicalizing([$current->id, $final->id]);
});

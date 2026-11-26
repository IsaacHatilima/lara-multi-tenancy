<?php

use App\Enums\BillingInterval;
use App\Enums\PlanDeployment;
use App\Enums\PlanStatus;
use App\Models\Plan;
use App\Models\PlanFeature;
use App\Models\Subscription;
use App\Models\Tenant;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Stancl\Tenancy\Events\TenantCreated;

beforeEach(function () {
    Event::fake([TenantCreated::class]);
});

test('a code has only one active plan', function () {
    Plan::factory()->create(['code' => 'basic']);

    Plan::factory()->create(['code' => 'basic']);
})->throws(UniqueConstraintViolationException::class);

test('a code keeps any number of inactive versions beside its active plan', function () {
    Plan::factory()->inactive()->count(2)->create(['code' => 'basic']);
    Plan::factory()->create(['code' => 'basic']);

    expect(Plan::query()->where('code', 'basic')->count())->toBe(3);
});

test('hosted plans have only one default', function () {
    Plan::factory()->default()->create();

    Plan::factory()->default()->create();
})->throws(UniqueConstraintViolationException::class);

test('a self-hosted default is stored beside the hosted default', function () {
    Plan::factory()->default()->create();
    Plan::factory()->default()->create(['deployment' => PlanDeployment::SelfHosted]);

    expect(Plan::query()->where('is_default', true)->count())->toBe(2);
});

test('a plan stores its enums as their raw values and reads them back cast', function (array $attributes, array $raw) {
    $plan = Plan::factory()->create($attributes);

    expect((array) DB::table('plans')->where('id', $plan->id)->first(['status', 'deployment', 'interval']))->toBe($raw)
        ->and($plan->fresh()->only(array_keys($attributes)))->toBe($attributes);
})->with([
    'active hosted monthly' => [
        ['status' => PlanStatus::Active, 'deployment' => PlanDeployment::Hosted, 'interval' => BillingInterval::Month, 'is_public' => true, 'is_default' => false],
        ['status' => 'active', 'deployment' => 'hosted', 'interval' => 'month'],
    ],
    'inactive self-hosted yearly' => [
        ['status' => PlanStatus::Inactive, 'deployment' => PlanDeployment::SelfHosted, 'interval' => BillingInterval::Year, 'is_public' => false, 'is_default' => true],
        ['status' => 'inactive', 'deployment' => 'self_hosted', 'interval' => 'year'],
    ],
]);

test('a custom plan belongs to its tenant and a public plan to none', function () {
    $custom = Plan::factory()->custom()->create();

    expect($custom->fresh()->tenant)->toBeInstanceOf(Tenant::class)
        ->and($custom->fresh()->tenant->getTenantKey())->toBe($custom->tenant_id)
        ->and($custom->is_public)->toBeFalse()
        ->and(Plan::factory()->create()->fresh()->tenant)->toBeNull();
});

test('a plan returns its features with their limits', function () {
    $plan = Plan::factory()->create();
    $limited = PlanFeature::factory()->limit(15)->create(['plan_id' => $plan->id]);
    $unlimited = PlanFeature::factory()->create(['plan_id' => $plan->id]);

    expect($plan->features->pluck('pivot.limit', 'id')->all())->toBe([
        $limited->feature_id => 15,
        $unlimited->feature_id => null,
    ]);
});

test('a plan returns its subscriptions', function () {
    $plan = Plan::factory()->create();
    $subscriptions = Subscription::factory()->count(2)->create(['plan_id' => $plan->id]);
    Subscription::factory()->create();

    expect($plan->subscriptions->modelKeys())->toEqualCanonicalizing($subscriptions->modelKeys());
});

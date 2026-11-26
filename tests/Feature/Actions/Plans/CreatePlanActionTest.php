<?php

use App\Actions\Plans\CreatePlanAction;
use App\Data\Plans\CreatePlanData;
use App\Enums\BillingInterval;
use App\Models\Plan;
use App\Models\PlanFeature;
use App\Models\User;
use Illuminate\Support\Facades\Event;
use Stancl\Tenancy\Events\TenantCreated;

beforeEach(function () {
    Event::fake([TenantCreated::class]);
});

function planInput(?string $startFromPlanId = null): CreatePlanData
{
    return new CreatePlanData(
        code: 'team',
        name: 'Team',
        description: null,
        priceMinor: 2900,
        currency: 'USD',
        interval: BillingInterval::Year,
        trialDays: 7,
        isPublic: true,
        sortOrder: 5,
        startFromPlanId: $startFromPlanId,
    );
}

/**
 * @return array<int, int|null>
 */
function featureLimits(Plan $plan): array
{
    return PlanFeature::query()->where('plan_id', $plan->id)->orderBy('feature_id')->pluck('limit', 'feature_id')->all();
}

test('without a plan to start from the new plan has no features', function () {
    PlanFeature::factory()->create();

    $plan = app(CreatePlanAction::class)->handle(planInput(), User::factory()->create());

    expect(featureLimits($plan))->toBe([]);
});

test('starting from a plan copies its limited and unlimited features and leaves it unchanged', function () {
    $source = Plan::factory()->create();
    PlanFeature::factory()->limit(5)->create(['plan_id' => $source->id]);
    PlanFeature::factory()->create(['plan_id' => $source->id]);
    $sourceLimits = featureLimits($source);

    $plan = app(CreatePlanAction::class)->handle(planInput($source->id), User::factory()->create());

    expect($sourceLimits)->toContain(5, null)
        ->and(featureLimits($plan))->toBe($sourceLimits)
        ->and(featureLimits($source->fresh()))->toBe($sourceLimits);
});

test('a plan can start from a custom or inactive plan and keeps its own details', function (Plan $source) {
    PlanFeature::factory()->limit(3)->create(['plan_id' => $source->id]);

    $plan = app(CreatePlanAction::class)->handle(planInput($source->id), User::factory()->create());

    expect(featureLimits($plan))->toBe(featureLimits($source))
        ->and($plan->fresh())
        ->name->toBe('Team')
        ->price_minor->toBe(2900)
        ->currency->toBe('USD')
        ->interval->toBe(BillingInterval::Year)
        ->trial_days->toBe(7)
        ->is_public->toBeTrue()
        ->sort_order->toBe(5)
        ->tenant_id->toBeNull();
})->with([
    'custom' => fn (): Plan => Plan::factory()->custom()->create(['price_minor' => 100, 'currency' => 'EUR', 'trial_days' => 0, 'sort_order' => 0]),
    'inactive' => fn (): Plan => Plan::factory()->inactive()->create(['is_public' => false, 'interval' => BillingInterval::Month, 'sort_order' => 0]),
]);

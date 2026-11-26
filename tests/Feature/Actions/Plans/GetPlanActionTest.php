<?php

use App\Actions\Plans\GetPlanAction;
use App\Data\Plans\PlanFeatureResource;
use App\Models\Feature;
use App\Models\Plan;
use App\Models\PlanFeature;
use App\Models\Tenant;
use Illuminate\Support\Facades\Event;
use Stancl\Tenancy\Events\TenantCreated;

beforeEach(function () {
    Event::fake([TenantCreated::class]);
});

test('every feature row is listed by key, the plan\'s own with their limits', function () {
    $plan = Plan::factory()->create(['sort_order' => 0]);
    $storage = Feature::factory()->create(['key' => 'test-storage']);
    $reports = Feature::factory()->create(['key' => 'test-reports']);
    Feature::factory()->create(['key' => 'test-video']);
    PlanFeature::factory()->limit(5)->create(['plan_id' => $plan->id, 'feature_id' => $storage->id]);
    PlanFeature::factory()->create(['plan_id' => $plan->id, 'feature_id' => $reports->id]);
    PlanFeature::factory()->limit(9)->create(['feature_id' => Feature::factory()->create(['key' => 'test-api'])->id]);

    $features = app(GetPlanAction::class)->handle($plan)->features;

    expect($features)->each->toBeInstanceOf(PlanFeatureResource::class)
        ->and(array_map(fn (PlanFeatureResource $feature): array => $feature->toArray(), $features))->toBe([
            ['key' => 'test-api', 'unitLabel' => null, 'included' => false, 'limit' => null],
            ['key' => 'test-reports', 'unitLabel' => null, 'included' => true, 'limit' => null],
            ['key' => 'test-storage', 'unitLabel' => null, 'included' => true, 'limit' => 5],
            ['key' => 'test-video', 'unitLabel' => null, 'included' => false, 'limit' => null],
        ]);
});

test('a custom plan carries its company and a public plan none', function () {
    $custom = Plan::factory()->custom()->create([
        'tenant_id' => Tenant::factory()->create(['name' => 'Acme GmbH'])->id,
        'sort_order' => 0,
    ]);

    expect(app(GetPlanAction::class)->handle($custom)->tenantName)->toBe('Acme GmbH')
        ->and(app(GetPlanAction::class)->handle(Plan::factory()->create(['sort_order' => 0]))->tenantName)->toBeNull();
});

test('the plan\'s own fields are carried with its status label', function () {
    $plan = Plan::factory()->inactive()->create(['code' => 'pro', 'name' => 'Pro', 'description' => 'For larger teams', 'sort_order' => 2]);

    expect(app(GetPlanAction::class)->handle($plan))
        ->id->toBe($plan->id)
        ->code->toBe('pro')
        ->name->toBe('Pro')
        ->description->toBe('For larger teams')
        ->sortOrder->toBe(2)
        ->status->toBe('inactive')
        ->statusLabel->toBe('Inactive');
});

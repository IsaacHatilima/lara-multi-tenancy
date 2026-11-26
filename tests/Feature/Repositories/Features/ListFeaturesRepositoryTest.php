<?php

use App\Enums\PlanDeployment;
use App\Models\Feature;
use App\Models\Plan;
use App\Models\PlanFeature;
use App\Repositories\Features\ListFeaturesRepository;
use Illuminate\Support\Facades\Event;
use Stancl\Tenancy\Events\TenantCreated;

beforeEach(function () {
    Event::fake([TenantCreated::class]);
});

test('every feature row is listed by key', function () {
    Feature::factory()->create(['key' => 'test-storage']);
    Feature::factory()->create(['key' => 'test-reports']);

    expect(app(ListFeaturesRepository::class)->handle()->pluck('key')->all())->toBe(['test-reports', 'test-storage']);
});

test('each feature lists the active hosted plans that include it, in the plans list\'s order', function () {
    $reports = Feature::factory()->create(['key' => 'test-reports']);
    $pro = Plan::factory()->create(['name' => 'Pro', 'sort_order' => 2]);
    $custom = Plan::factory()->custom()->create(['name' => 'Acme', 'sort_order' => 1]);
    $basic = Plan::factory()->create(['name' => 'Basic', 'sort_order' => 1]);
    $retired = Plan::factory()->inactive()->create(['name' => 'Basic', 'sort_order' => 1]);
    $selfHosted = Plan::factory()->create(['deployment' => PlanDeployment::SelfHosted, 'sort_order' => 0]);
    foreach ([$pro, $custom, $basic, $retired, $selfHosted] as $plan) {
        PlanFeature::factory()->create(['plan_id' => $plan->id, 'feature_id' => $reports->id]);
    }
    Plan::factory()->create(['sort_order' => 0]);

    $plans = app(ListFeaturesRepository::class)->handle()->sole()->plans;

    expect($plans->pluck('id')->all())->toBe([$custom->id, $basic->id, $pro->id])
        ->and($plans[0]->relationLoaded('tenant'))->toBeTrue();
});

test('a feature no active plan includes lists no plans', function () {
    PlanFeature::factory()->create([
        'plan_id' => Plan::factory()->inactive()->create()->id,
        'feature_id' => Feature::factory()->create(['key' => 'test-reports'])->id,
    ]);

    expect(app(ListFeaturesRepository::class)->handle()->sole()->plans)->toBeEmpty();
});

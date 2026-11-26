<?php

use App\Actions\Features\ListFeaturesAction;
use App\Data\Features\FeaturePlanResource;
use App\Data\Features\FeatureResource;
use App\Models\Feature;
use App\Models\Plan;
use App\Models\PlanFeature;
use App\Models\Tenant;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Stancl\Tenancy\Events\TenantCreated;

beforeEach(function () {
    Event::fake([TenantCreated::class]);
});

test('features are listed as resources, a custom plan with its company and a public plan without', function () {
    $reports = Feature::factory()->create(['key' => 'test-reports']);
    $basic = Plan::factory()->create(['code' => 'basic', 'name' => 'Basic', 'sort_order' => 0]);
    $custom = Plan::factory()->custom()->create([
        'code' => 'acme',
        'name' => 'Acme',
        'tenant_id' => Tenant::factory()->create(['name' => 'Acme GmbH'])->id,
        'sort_order' => 1,
    ]);
    foreach ([$basic, $custom] as $plan) {
        PlanFeature::factory()->create(['plan_id' => $plan->id, 'feature_id' => $reports->id]);
    }

    DB::enableQueryLog();
    $features = app(ListFeaturesAction::class)->handle();

    expect(DB::getQueryLog())->toHaveCount(3)
        ->and($features)->toHaveCount(1)->each->toBeInstanceOf(FeatureResource::class)
        ->and($features[0])->key->toBe('test-reports')->unitLabel->toBeNull()
        ->and($features[0]->plans)->each->toBeInstanceOf(FeaturePlanResource::class)
        ->and(array_map(fn (FeaturePlanResource $plan): array => $plan->toArray(), $features[0]->plans))->toBe([
            ['id' => $basic->id, 'code' => 'basic', 'name' => 'Basic', 'tenantName' => null],
            ['id' => $custom->id, 'code' => 'acme', 'name' => 'Acme', 'tenantName' => 'Acme GmbH'],
        ]);
});

test('without feature rows the list is empty', function () {
    expect(app(ListFeaturesAction::class)->handle())->toBe([]);
});

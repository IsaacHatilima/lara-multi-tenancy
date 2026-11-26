<?php

use App\Enums\PlanDeployment;
use App\Models\Feature;
use App\Models\Plan;
use App\Models\PlanFeature;
use App\Repositories\Features\ListPlanCodesByFeatureRepository;
use Illuminate\Support\Facades\Event;
use Stancl\Tenancy\Events\TenantCreated;

beforeEach(function () {
    Event::fake([TenantCreated::class]);
});

test('each feature maps to the offered plans that include it, in their order', function () {
    $video = Feature::factory()->create(['key' => 'test-video']);
    $reports = Feature::factory()->create(['key' => 'test-reports']);
    Feature::factory()->create(['key' => 'test-unused']);
    $plans = [
        'pro' => Plan::factory()->create(['code' => 'pro', 'sort_order' => 2]),
        'business' => Plan::factory()->create(['code' => 'business', 'sort_order' => 2]),
        'basic' => Plan::factory()->create(['code' => 'basic', 'sort_order' => 1]),
        'retired' => Plan::factory()->inactive()->create(['code' => 'basic', 'sort_order' => 0]),
        'hidden' => Plan::factory()->create(['code' => 'hidden', 'is_public' => false]),
        'custom' => Plan::factory()->custom()->create(['code' => 'custom']),
        'self-hosted' => Plan::factory()->create(['code' => 'self-hosted', 'deployment' => PlanDeployment::SelfHosted]),
    ];

    foreach ($plans as $plan) {
        PlanFeature::factory()->create(['plan_id' => $plan->id, 'feature_id' => $video->id]);
    }
    PlanFeature::factory()->create(['plan_id' => $plans['pro']->id, 'feature_id' => $reports->id]);

    expect(app(ListPlanCodesByFeatureRepository::class)->handle())->toBe([
        'test-video' => ['basic', 'business', 'pro'],
        'test-reports' => ['pro'],
    ]);
});

<?php

use App\Models\Feature;
use App\Models\Plan;
use App\Models\PlanFeature;
use App\Repositories\Features\ListPlanFeatureLimitsRepository;

test('it maps each feature the plan includes to its limit', function () {
    $plan = Plan::factory()->create();
    $attachments = Feature::factory()->create(['key' => 'test-attachments']);
    $reports = Feature::factory()->create(['key' => 'test-reports']);
    Feature::factory()->create(['key' => 'test-video']);
    PlanFeature::factory()->limit(15)->create(['plan_id' => $plan->id, 'feature_id' => $attachments->id]);
    PlanFeature::factory()->create(['plan_id' => $plan->id, 'feature_id' => $reports->id]);
    PlanFeature::factory()->limit(50)->create(['feature_id' => $attachments->id]);

    $limits = app(ListPlanFeatureLimitsRepository::class)->handle($plan->id);

    expect($limits)->toHaveCount(2)->toHaveKeys(['test-attachments', 'test-reports'])
        ->and($limits['test-attachments'])->toBe(15)
        ->and($limits['test-reports'])->toBeNull();
});

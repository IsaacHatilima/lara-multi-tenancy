<?php

use App\Actions\Features\IsFeatureOnDefaultPlanAction;
use App\Enums\PlanDeployment;
use App\Models\Feature;
use App\Models\Plan;
use App\Models\PlanFeature;

beforeEach(function () {
    $this->reports = Feature::factory()->create(['key' => 'test-reports']);
    $this->include = function (Plan $plan): void {
        PlanFeature::factory()->create(['plan_id' => $plan->id, 'feature_id' => $this->reports->id]);
    };
});

test('a key the hosted default plan includes is on it', function () {
    ($this->include)(Plan::factory()->default()->create());

    expect(app(IsFeatureOnDefaultPlanAction::class)->handle('test-reports'))->toBeTrue();
});

test('a key only another plan includes is not on it', function () {
    Plan::factory()->default()->create();
    ($this->include)(Plan::factory()->create());

    expect(app(IsFeatureOnDefaultPlanAction::class)->handle('test-reports'))->toBeFalse();
});

test('no key is on it when there is no hosted default plan', function () {
    ($this->include)(Plan::factory()->default()->create(['deployment' => PlanDeployment::SelfHosted]));

    expect(app(IsFeatureOnDefaultPlanAction::class)->handle('test-reports'))->toBeFalse();
});

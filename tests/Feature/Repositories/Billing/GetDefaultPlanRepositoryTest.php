<?php

use App\Enums\PlanDeployment;
use App\Models\Plan;
use App\Repositories\Billing\GetDefaultPlanRepository;

test('the hosted default plan is returned and a self-hosted default is ignored', function () {
    Plan::factory()->create();
    Plan::factory()->default()->create(['deployment' => PlanDeployment::SelfHosted]);
    $default = Plan::factory()->default()->create(['name' => 'Free', 'code' => 'free']);

    $plan = app(GetDefaultPlanRepository::class)->handle();

    expect($plan?->only(['id', 'name', 'code']))->toBe(['id' => $default->id, 'name' => 'Free', 'code' => 'free']);
});

test('without a hosted default plan there is no default', function () {
    Plan::factory()->create();
    Plan::factory()->default()->create(['deployment' => PlanDeployment::SelfHosted]);

    expect(app(GetDefaultPlanRepository::class)->handle())->toBeNull();
});

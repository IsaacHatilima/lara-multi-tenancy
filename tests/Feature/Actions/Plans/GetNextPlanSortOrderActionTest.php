<?php

use App\Actions\Plans\GetNextPlanSortOrderAction;
use App\Enums\PlanDeployment;
use App\Models\Plan;

test('the next sort order follows the highest of any plan', function () {
    Plan::factory()->create(['sort_order' => 2]);
    Plan::factory()->inactive()->create(['sort_order' => 7]);
    Plan::factory()->create(['deployment' => PlanDeployment::SelfHosted, 'sort_order' => 4]);

    expect(app(GetNextPlanSortOrderAction::class)->handle())->toBe(8);
});

test('without plans the next sort order is 0', function () {
    expect(app(GetNextPlanSortOrderAction::class)->handle())->toBe(0);
});

test('after a single plan at 0 the next sort order is 1', function () {
    Plan::factory()->create(['sort_order' => 0]);

    expect(app(GetNextPlanSortOrderAction::class)->handle())->toBe(1);
});

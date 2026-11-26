<?php

use App\Models\Plan;
use App\Models\Subscription;
use App\Models\Tenant;
use App\Repositories\Billing\ListTenantIdsOnPlanRepository;
use Illuminate\Support\Facades\Event;
use Stancl\Tenancy\Events\TenantCreated;

beforeEach(function () {
    Event::fake([TenantCreated::class]);
});

test('only tenants currently subscribed to the plan are listed', function () {
    $plan = Plan::factory()->create();
    $trialing = Subscription::factory()->trialing()->create(['plan_id' => $plan->id]);
    $readOnly = Subscription::factory()->readOnly()->create(['plan_id' => $plan->id]);
    $former = Tenant::factory()->create();
    Subscription::factory()->canceled()->create(['tenant_id' => $former->id, 'plan_id' => $plan->id]);
    Subscription::factory()->ended()->create(['tenant_id' => $former->id, 'plan_id' => $plan->id]);
    Subscription::factory()->active()->create();

    expect(app(ListTenantIdsOnPlanRepository::class)->handle($plan->id))
        ->toEqualCanonicalizing([$trialing->tenant_id, $readOnly->tenant_id]);
});

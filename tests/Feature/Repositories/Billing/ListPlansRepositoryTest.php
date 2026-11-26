<?php

use App\Data\Plans\PlanResource;
use App\Enums\PlanDeployment;
use App\Models\Plan;
use App\Models\Tenant;
use App\Repositories\Billing\ListPlansRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Stancl\Tenancy\Events\TenantCreated;

beforeEach(function () {
    Event::fake([TenantCreated::class]);
});

test('hosted plans are listed by sort order, then name, then id', function () {
    $pro = Plan::factory()->create(['name' => 'Pro', 'sort_order' => 2]);
    $basic = Plan::factory()->inactive()->create(['name' => 'Basic', 'sort_order' => 1]);
    $acme = Plan::factory()->custom()->create(['name' => 'Acme', 'sort_order' => 1]);
    $free = Plan::factory()->create(['name' => 'Free', 'sort_order' => 0]);
    Plan::factory()->create(['deployment' => PlanDeployment::SelfHosted, 'sort_order' => 0]);

    $plans = app(ListPlansRepository::class)->handle();

    expect($plans->pluck('id')->all())->toBe([$free->id, $acme->id, $basic->id, $pro->id]);
});

test('plans with the same sort order and name are listed by id', function () {
    $ids = Plan::factory()->count(3)->inactive()->create(['code' => 'basic', 'name' => 'Basic'])->pluck('id')->sort()->values()->all();

    expect(app(ListPlansRepository::class)->handle()->pluck('id')->all())->toBe($ids);
});

test('a custom plan comes with its tenant, so building the resources runs no further queries', function () {
    $tenant = Tenant::factory()->create(['name' => 'Acme']);
    Plan::factory()->custom()->create(['tenant_id' => $tenant->id]);
    Plan::factory()->create();

    $plans = app(ListPlansRepository::class)->handle();
    DB::connection()->enableQueryLog();
    $tenantNames = $plans->map(fn (Plan $plan): ?string => PlanResource::fromModel($plan)->tenantName)->all();

    expect($tenantNames)->toEqualCanonicalizing(['Acme', null])
        ->and(DB::connection()->getQueryLog())->toBe([]);
});

<?php

use App\Actions\Plans\ListPlansAction;
use App\Data\Plans\PlanResource;
use App\Models\Plan;
use App\Models\Tenant;
use Illuminate\Support\Facades\Event;
use Stancl\Tenancy\Events\TenantCreated;

beforeEach(function () {
    Event::fake([TenantCreated::class]);
});

test('plans are listed as resources, a custom plan with its company name', function () {
    $public = Plan::factory()->create(['sort_order' => 0]);
    $custom = Plan::factory()->custom()->create([
        'tenant_id' => Tenant::factory()->create(['name' => 'Acme'])->id,
        'sort_order' => 1,
    ]);

    $plans = app(ListPlansAction::class)->handle();

    expect($plans)->toHaveCount(2)->each->toBeInstanceOf(PlanResource::class)
        ->and($plans[0])->id->toBe($public->id)->tenantName->toBeNull()
        ->and($plans[1])->id->toBe($custom->id)->tenantName->toBe('Acme');
});

test('without plans the list is empty', function () {
    expect(app(ListPlansAction::class)->handle())->toBe([]);
});

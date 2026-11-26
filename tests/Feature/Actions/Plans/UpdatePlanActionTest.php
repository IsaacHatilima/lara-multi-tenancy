<?php

use App\Actions\Features\GetFeatureSetAction;
use App\Actions\Plans\UpdatePlanAction;
use App\Data\Plans\UpdatePlanData;
use App\Models\Feature;
use App\Models\Plan;
use App\Models\PlanFeature;
use App\Models\Subscription;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Event;
use Stancl\Tenancy\Events\TenantCreated;

beforeEach(function () {
    Event::fake([TenantCreated::class]);
    config(['mitarbo.managed_deployment' => true, 'tenancy.identification.central_domains' => ['mitarbo.test']]);

    $this->free = Plan::factory()->default()->create(['code' => 'free', 'name' => 'Free', 'sort_order' => 0]);
    $this->basic = Plan::factory()->create(['code' => 'basic', 'name' => 'Basic', 'sort_order' => 1]);
    Feature::factory()->create(['key' => 'test-reports']);
    $this->onBasic = Tenant::query()->findOrFail(Subscription::factory()->active()->create(['plan_id' => $this->basic->id])->tenant_id);
    $this->onOtherPlan = Tenant::query()->findOrFail(Subscription::factory()->active()->create()->tenant_id);
    $this->unsubscribed = Tenant::factory()->create();

    foreach ([$this->onBasic, $this->onOtherPlan, $this->unsubscribed] as $tenant) {
        app(GetFeatureSetAction::class)->handle('acme.mitarbo.test', $tenant);
    }

    $this->cached = fn (Tenant $tenant): bool => Cache::store('central')->has('tenant-features:'.$tenant->id);
});

/**
 * @param  array<string, int|null>  $featureLimits
 */
function planChange(Plan $plan, array $featureLimits): UpdatePlanData
{
    return new UpdatePlanData(name: $plan->name, description: null, sortOrder: $plan->sort_order, featureLimits: $featureLimits);
}

test('an update clears the tenants subscribed to the plan and keeps tenants on other plans', function () {
    app(UpdatePlanAction::class)->handle($this->basic, planChange($this->basic, ['test-reports' => 5]), User::factory()->create());

    expect(($this->cached)($this->onBasic))->toBeFalse()
        ->and(($this->cached)($this->onOtherPlan))->toBeTrue()
        ->and(($this->cached)($this->unsubscribed))->toBeTrue();
});

test('an update to the default plan\'s features also clears tenants without a subscription', function () {
    app(UpdatePlanAction::class)->handle($this->free, planChange($this->free, ['test-reports' => null]), User::factory()->create());

    expect(($this->cached)($this->unsubscribed))->toBeFalse()
        ->and(($this->cached)($this->onOtherPlan))->toBeTrue();
});

test('a failed update clears nothing', function () {
    PlanFeature::creating(fn () => throw new RuntimeException('Save failed.'));

    expect(fn () => app(UpdatePlanAction::class)->handle(
        $this->free,
        new UpdatePlanData(name: 'Starter', description: null, sortOrder: 0, featureLimits: ['test-reports' => 5]),
        User::factory()->create(),
    ))->toThrow(RuntimeException::class, 'Save failed.');

    expect(($this->cached)($this->onBasic))->toBeTrue()
        ->and(($this->cached)($this->onOtherPlan))->toBeTrue()
        ->and(($this->cached)($this->unsubscribed))->toBeTrue();
});

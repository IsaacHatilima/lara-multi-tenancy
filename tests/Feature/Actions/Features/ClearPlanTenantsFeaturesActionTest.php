<?php

use App\Actions\Features\ClearPlanTenantsFeaturesAction;
use App\Data\Features\FeatureSet;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\Tenant;
use App\Repositories\Features\TenantFeatureSetCache;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Event;
use Stancl\Tenancy\Events\TenantCreated;

beforeEach(function () {
    Event::fake([TenantCreated::class]);

    $this->plan = Plan::factory()->create();
    $this->onPlan = Subscription::factory()->active()->create(['plan_id' => $this->plan->id])->tenant_id;
    $this->onOtherPlan = Subscription::factory()->active()->create()->tenant_id;
    $this->unsubscribed = Tenant::factory()->create()->id;

    foreach ([$this->onPlan, $this->onOtherPlan, $this->unsubscribed] as $tenantId) {
        app(TenantFeatureSetCache::class)->remember($tenantId, fn (): FeatureSet => FeatureSet::ungated());
    }

    $this->cached = fn (string $tenantId): bool => Cache::store('central')->has('tenant-features:'.$tenantId);
});

test('it clears the tenants on the plan and keeps everyone else', function () {
    app(ClearPlanTenantsFeaturesAction::class)->handle($this->plan->id, false);

    expect(($this->cached)($this->onPlan))->toBeFalse()
        ->and(($this->cached)($this->onOtherPlan))->toBeTrue()
        ->and(($this->cached)($this->unsubscribed))->toBeTrue();
});

test('for the default plan it also clears tenants without a current subscription', function (bool $isDefault, bool $wasDefault) {
    $this->plan->updateQuietly(['is_default' => $isDefault]);

    app(ClearPlanTenantsFeaturesAction::class)->handle($this->plan->id, $wasDefault);

    expect(($this->cached)($this->onPlan))->toBeFalse()
        ->and(($this->cached)($this->unsubscribed))->toBeFalse()
        ->and(($this->cached)($this->onOtherPlan))->toBeTrue();
})->with([
    'the hosted default' => [true, false],
    'the former default' => [false, true],
]);

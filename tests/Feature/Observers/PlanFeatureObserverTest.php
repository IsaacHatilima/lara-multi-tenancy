<?php

use App\Actions\Features\GetFeatureSetAction;
use App\Models\Feature;
use App\Models\Plan;
use App\Models\PlanFeature;
use App\Models\Subscription;
use App\Models\Tenant;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Stancl\Tenancy\Events\TenantCreated;

beforeEach(function () {
    Event::fake([TenantCreated::class]);
    config(['mitarbo.managed_deployment' => true, 'tenancy.identification.central_domains' => ['mitarbo.test']]);

    $this->free = Plan::factory()->default()->create(['code' => 'free']);
    $this->basic = Plan::factory()->create(['code' => 'basic']);
    $this->reports = Feature::factory()->create(['key' => 'test-reports']);
    $this->video = Feature::factory()->create(['key' => 'test-video']);
    $this->mapping = PlanFeature::factory()->create(['plan_id' => $this->basic->id, 'feature_id' => $this->reports->id]);
    $this->onBasic = Tenant::query()->findOrFail(Subscription::factory()->active()->create(['plan_id' => $this->basic->id])->tenant_id);
    $this->onOtherPlan = Tenant::query()->findOrFail(Subscription::factory()->active()->create()->tenant_id);
    $this->unsubscribed = Tenant::factory()->create();

    foreach ([$this->onBasic, $this->onOtherPlan, $this->unsubscribed] as $tenant) {
        app(GetFeatureSetAction::class)->handle('acme.mitarbo.test', $tenant);
    }

    $this->cached = fn (Tenant $tenant): bool => Cache::store('central')->has('tenant-features:'.$tenant->id);
});

test('a mapping write clears the tenants on its plan and keeps tenants on other plans', function (Closure $write) {
    $write($this->mapping, $this->video);

    expect(($this->cached)($this->onBasic))->toBeFalse()
        ->and(($this->cached)($this->onOtherPlan))->toBeTrue()
        ->and(($this->cached)($this->unsubscribed))->toBeTrue();
})->with([
    'created' => fn (PlanFeature $mapping, Feature $video) => PlanFeature::factory()->create(['plan_id' => $mapping->plan_id, 'feature_id' => $video->id]),
    'updated' => fn (PlanFeature $mapping) => $mapping->forceFill(['limit' => 10])->save(),
    'deleted' => fn (PlanFeature $mapping) => $mapping->delete(),
]);

test('a write through the plan features relation clears the tenants on that plan', function (Closure $write) {
    $write($this->basic, $this->reports, $this->video);

    expect(($this->cached)($this->onBasic))->toBeFalse()
        ->and(($this->cached)($this->onOtherPlan))->toBeTrue();
})->with([
    'attach' => fn (Plan $plan, Feature $reports, Feature $video) => $plan->features()->attach($video->id, ['limit' => 5]),
    'detach by id' => fn (Plan $plan, Feature $reports) => $plan->features()->detach([$reports->id]),
    'detach all' => fn (Plan $plan) => $plan->features()->detach(),
    'sync' => fn (Plan $plan, Feature $reports, Feature $video) => $plan->features()->sync([$video->id]),
    'limit change' => fn (Plan $plan, Feature $reports) => $plan->features()->updateExistingPivot($reports->id, ['limit' => 10]),
]);

test('a mapping change on the default plan also clears tenants without a subscription', function () {
    $this->free->features()->attach($this->reports->id);

    expect(($this->cached)($this->unsubscribed))->toBeFalse()
        ->and(($this->cached)($this->onOtherPlan))->toBeTrue();
});

test('a rolled back attach clears nothing', function () {
    try {
        DB::connection(config('tenancy.database.central_connection'))->transaction(function () {
            $this->basic->features()->attach($this->video->id);

            throw new RuntimeException('Rolled back.');
        });
    } catch (RuntimeException) {
    }

    expect(($this->cached)($this->onBasic))->toBeTrue();
});

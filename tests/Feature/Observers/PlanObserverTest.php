<?php

use App\Actions\Features\GetFeatureSetAction;
use App\Models\Plan;
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
    $this->onBasic = Tenant::query()->findOrFail(Subscription::factory()->active()->create(['plan_id' => $this->basic->id])->tenant_id);
    $this->onOtherPlan = Tenant::query()->findOrFail(Subscription::factory()->active()->create()->tenant_id);
    $this->unsubscribed = Tenant::factory()->create();

    foreach ([$this->onBasic, $this->onOtherPlan, $this->unsubscribed] as $tenant) {
        app(GetFeatureSetAction::class)->handle('acme.mitarbo.test', $tenant);
    }

    $this->cached = fn (Tenant $tenant): bool => Cache::store('central')->has('tenant-features:'.$tenant->id);
});

test('updating a plan clears its tenants and keeps tenants on other plans', function () {
    $this->basic->update(['name' => 'Basic plus']);

    expect(($this->cached)($this->onBasic))->toBeFalse()
        ->and(($this->cached)($this->onOtherPlan))->toBeTrue()
        ->and(($this->cached)($this->unsubscribed))->toBeTrue();
});

test('updating the default plan also clears tenants without a subscription', function () {
    $this->free->update(['name' => 'Free plus']);

    expect(($this->cached)($this->unsubscribed))->toBeFalse()
        ->and(($this->cached)($this->onBasic))->toBeTrue();
});

test('moving the default flag clears tenants without a subscription', function (Closure $move) {
    $move($this->free, $this->basic);

    expect(($this->cached)($this->unsubscribed))->toBeFalse();
})->with([
    'a plan becomes the default' => function (Plan $free, Plan $basic): void {
        $free->updateQuietly(['is_default' => false]);
        $basic->update(['is_default' => true]);
    },
    'the default stops being the default' => fn (Plan $free) => $free->update(['is_default' => false]),
]);

test('deleting the default plan clears tenants without a subscription', function () {
    $this->free->delete();

    expect(($this->cached)($this->unsubscribed))->toBeFalse()
        ->and(($this->cached)($this->onOtherPlan))->toBeTrue();
});

test('a rolled back plan update clears nothing', function () {
    try {
        DB::connection(config('tenancy.database.central_connection'))->transaction(function () {
            $this->free->update(['name' => 'Free plus']);
            $this->basic->update(['name' => 'Basic plus']);

            throw new RuntimeException('Rolled back.');
        });
    } catch (RuntimeException) {
    }

    expect(($this->cached)($this->onBasic))->toBeTrue()
        ->and(($this->cached)($this->unsubscribed))->toBeTrue();
});

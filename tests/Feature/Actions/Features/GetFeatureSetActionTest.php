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

    $this->reports = Feature::factory()->create(['key' => 'test-reports']);
    $this->onPlan = function (string $code): Tenant {
        $plan = Plan::factory()->create(['name' => ucfirst($code), 'code' => $code]);
        PlanFeature::factory()->create(['plan_id' => $plan->id, 'feature_id' => $this->reports->id, 'limit' => strlen($code)]);
        $subscription = Subscription::factory()->active()->create(['plan_id' => $plan->id]);

        return Tenant::query()->findOrFail($subscription->tenant_id);
    };
    $this->cacheEvents = [];
    Event::listen('Illuminate\Cache\Events\*', function (string $event) {
        $this->cacheEvents[] = $event;
    });
});

test('an ungated host gets the ungated set without reading the database or the cache', function (bool $managed, string $host) {
    config(['mitarbo.managed_deployment' => $managed]);
    $tenant = ($this->onPlan)('basic');
    $this->cacheEvents = [];
    DB::connection()->enableQueryLog();

    $set = app(GetFeatureSetAction::class)->handle($host, $tenant);

    expect($set->gated)->toBeFalse()
        ->and($set->allows('test-reports', PHP_INT_MAX))->toBeTrue()
        ->and(DB::connection()->getQueryLog())->toBe([])
        ->and($this->cacheEvents)->toBe([])
        ->and(Cache::store('central')->has('tenant-features:'.$tenant->id))->toBeFalse();
})->with([
    'managed central host' => [true, 'mitarbo.test'],
    'managed central host in upper case' => [true, 'MITARBO.test'],
    'self-hosted, central host' => [false, 'mitarbo.test'],
    'self-hosted, another host' => [false, 'portal.acme.example'],
]);

test('a tenant domain gets that tenant gated set', function (string $host) {
    $tenant = ($this->onPlan)('basic');

    $set = app(GetFeatureSetAction::class)->handle($host, $tenant);

    expect($set->gated)->toBeTrue()
        ->and($set->planCode)->toBe('basic')
        ->and($set->allows('test-reports', 6))->toBeFalse();
})->with([
    'tenant subdomain' => 'acme.mitarbo.test',
    'custom tenant domain' => 'portal.acme.example',
]);

test('a gated host without a tenant refuses to answer', function () {
    app(GetFeatureSetAction::class)->handle('acme.mitarbo.test', null);
})->throws(LogicException::class);

test('tenants resolved in one request each get and cache their own set', function () {
    $basic = ($this->onPlan)('basic');
    $pro = ($this->onPlan)('pro');

    $basicSet = app(GetFeatureSetAction::class)->handle('basic.mitarbo.test', $basic);
    $proSet = app(GetFeatureSetAction::class)->handle('pro.mitarbo.test', $pro);

    expect($basicSet->planCode)->toBe('basic')
        ->and($basicSet->limit('test-reports'))->toBe(5)
        ->and($proSet->planCode)->toBe('pro')
        ->and($proSet->limit('test-reports'))->toBe(3)
        ->and(Cache::store('central')->get('tenant-features:'.$basic->id)['planCode'])->toBe('basic')
        ->and(Cache::store('central')->get('tenant-features:'.$pro->id)['planCode'])->toBe('pro');
});

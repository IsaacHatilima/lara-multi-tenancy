<?php

use App\Actions\Features\GetFeatureSetAction;
use App\Enums\SubscriptionStatus;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\Tenant;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Stancl\Tenancy\Bootstrappers\CacheTenancyBootstrapper;
use Stancl\Tenancy\Events\TenantCreated;

beforeEach(function () {
    Event::fake([TenantCreated::class]);
    config(['mitarbo.managed_deployment' => true, 'tenancy.identification.central_domains' => ['mitarbo.test']]);

    $this->basic = Plan::factory()->create(['code' => 'basic']);
    $this->pro = Plan::factory()->create(['code' => 'pro']);
    $this->tenant = Tenant::factory()->create();
    $this->other = Tenant::factory()->create();
    $this->resolve = fn (Tenant $tenant) => app(GetFeatureSetAction::class)->handle('acme.mitarbo.test', $tenant);
    $this->cached = fn (Tenant $tenant): bool => Cache::store('central')->has('tenant-features:'.$tenant->id);
});

afterEach(function () {
    tenancy()->end();
});

test('a subscription write clears its tenant entry without rebuilding it', function (Closure $write) {
    $subscription = Subscription::factory()->active()->create(['tenant_id' => $this->tenant->id, 'plan_id' => $this->basic->id]);
    ($this->resolve)($this->tenant);

    $write($subscription);

    expect(($this->cached)($this->tenant))->toBeFalse();
})->with([
    'created' => fn (Subscription $subscription) => Subscription::factory()->ended()->create(['tenant_id' => $subscription->tenant_id]),
    'updated' => fn (Subscription $subscription) => $subscription->forceFill(['status' => SubscriptionStatus::PastDue])->save(),
    'deleted' => fn (Subscription $subscription) => $subscription->delete(),
]);

test('a subscription write leaves other tenants cached', function () {
    ($this->resolve)($this->other);

    Subscription::factory()->active()->create(['tenant_id' => $this->tenant->id]);

    expect(($this->cached)($this->other))->toBeTrue();
});

test('a rolled back subscription update leaves the entry in place', function () {
    $subscription = Subscription::factory()->active()->create(['tenant_id' => $this->tenant->id, 'plan_id' => $this->basic->id]);
    ($this->resolve)($this->tenant);

    try {
        DB::connection(config('tenancy.database.central_connection'))->transaction(function () use ($subscription) {
            $subscription->forceFill(['plan_id' => $this->pro->id])->save();

            throw new RuntimeException('Rolled back.');
        });
    } catch (RuntimeException) {
    }

    expect(($this->cached)($this->tenant))->toBeTrue()
        ->and(($this->resolve)($this->tenant)->planCode)->toBe('basic');
});

test('an entry built during the tenant request is the one a central update clears', function () {
    config([
        'tenancy.bootstrappers' => [CacheTenancyBootstrapper::class],
        'tenancy.cache.stores' => ['database'],
        'cache.default' => 'database',
        'cache.stores.database.connection' => 'sqlite',
        'cache.stores.central' => [...config('cache.stores.database'), 'connection' => 'sqlite'],
    ]);
    $subscription = Subscription::factory()->active()->create(['tenant_id' => $this->tenant->id, 'plan_id' => $this->basic->id]);

    tenancy()->initialize($this->tenant);
    expect(($this->resolve)(tenant())->planCode)->toBe('basic')
        ->and(Cache::has('tenant-features:'.$this->tenant->id))->toBeFalse();
    tenancy()->end();
    app()->forgetScopedInstances();

    expect(($this->cached)($this->tenant))->toBeTrue();

    $subscription->forceFill(['plan_id' => $this->pro->id])->save();

    expect(($this->cached)($this->tenant))->toBeFalse();

    app()->forgetScopedInstances();
    tenancy()->initialize($this->tenant);

    expect(($this->resolve)(tenant())->planCode)->toBe('pro');
});

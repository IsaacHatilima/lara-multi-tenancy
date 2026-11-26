<?php

use App\Data\Features\FeatureItem;
use App\Data\Features\FeatureSet;
use App\Data\Features\SubscriptionState;
use App\Enums\LimitUnit;
use App\Enums\SubscriptionStatus;
use App\Repositories\Features\TenantFeatureSetCache;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Event;

beforeEach(function () {
    $this->set = FeatureSet::gated(
        planName: 'Basic',
        planCode: 'basic',
        subscription: new SubscriptionState(SubscriptionStatus::Trialing, CarbonImmutable::parse('2026-12-01 10:00:00'), null),
        readOnly: false,
        items: ['test-attachments' => new FeatureItem(enabled: true, limit: 15, unit: LimitUnit::Megabytes, availableOn: [])],
    );
    $this->builds = 0;
    $this->build = function (): FeatureSet {
        $this->builds++;

        return $this->set;
    };
    $this->cacheEvents = [];
    Event::listen('Illuminate\Cache\Events\*', function (string $event) {
        $this->cacheEvents[] = $event;
    });
});

test('the set is stored as plain data in the central store under the tenant key', function () {
    app(TenantFeatureSetCache::class)->remember('acme', $this->build);

    $entry = Cache::store('central')->get('tenant-features:acme');

    expect($entry)->toBe($this->set->toArray());

    array_walk_recursive($entry, function (mixed $value): void {
        expect(is_object($value))->toBeFalse();
    });
});

test('a later request within ten minutes reads the cached set without building it', function () {
    app(TenantFeatureSetCache::class)->remember('acme', $this->build);
    $this->travel(9)->minutes();
    app()->forgetScopedInstances();

    $set = app(TenantFeatureSetCache::class)->remember('acme', $this->build);

    expect($set)->toEqual($this->set)
        ->and($this->builds)->toBe(1);
});

test('after ten minutes the set is built again', function () {
    app(TenantFeatureSetCache::class)->remember('acme', $this->build);
    $this->travel(10)->minutes();
    app()->forgetScopedInstances();

    app(TenantFeatureSetCache::class)->remember('acme', $this->build);

    expect($this->builds)->toBe(2);
});

test('within one request the set is memoised without reading the store', function () {
    $cache = app(TenantFeatureSetCache::class);
    $cache->remember('acme', $this->build);
    $this->cacheEvents = [];

    $set = app(TenantFeatureSetCache::class)->remember('acme', $this->build);

    expect($set)->toBe($cache->remember('acme', $this->build))
        ->and($this->cacheEvents)->toBe([])
        ->and($this->builds)->toBe(1);
});

test('the memo ends with the request', function () {
    app(TenantFeatureSetCache::class)->remember('acme', $this->build);
    app()->forgetScopedInstances();
    $this->cacheEvents = [];

    app(TenantFeatureSetCache::class)->remember('acme', $this->build);

    expect($this->cacheEvents)->not->toBe([])
        ->and($this->builds)->toBe(1);
});

test('forgetting tenants removes their entries and memos and keeps the others', function () {
    $cache = app(TenantFeatureSetCache::class);
    $cache->remember('acme', $this->build);
    $cache->remember('globex', $this->build);
    $cache->remember('initech', $this->build);

    $cache->forget(['acme', 'globex']);

    expect(Cache::store('central')->has('tenant-features:acme'))->toBeFalse()
        ->and(Cache::store('central')->has('tenant-features:globex'))->toBeFalse()
        ->and(Cache::store('central')->has('tenant-features:initech'))->toBeTrue();

    $cache->remember('acme', $this->build);
    $cache->remember('initech', $this->build);

    expect($this->builds)->toBe(4);
});

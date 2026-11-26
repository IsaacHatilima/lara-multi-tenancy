<?php

use App\Data\Features\FeatureItem;
use App\Data\Features\FeatureSet;
use App\Data\Features\SubscriptionState;
use App\Enums\LimitUnit;
use App\Enums\SubscriptionStatus;
use Carbon\CarbonImmutable;

function featureItems(): array
{
    return [
        'attachments' => new FeatureItem(enabled: true, limit: 15, unit: LimitUnit::Megabytes, availableOn: []),
        'reports' => new FeatureItem(enabled: true, limit: null, unit: null, availableOn: []),
        'video' => new FeatureItem(enabled: false, limit: null, unit: null, availableOn: ['pro']),
    ];
}

function trialState(): SubscriptionState
{
    return new SubscriptionState(
        SubscriptionStatus::Trialing,
        CarbonImmutable::parse('2026-12-01 10:00:00'),
        CarbonImmutable::parse('2026-12-08 10:00:00'),
    );
}

function featureSet(array $overrides = []): FeatureSet
{
    return FeatureSet::gated(...[
        'planName' => 'Basic',
        'planCode' => 'basic',
        'subscription' => trialState(),
        'readOnly' => false,
        'items' => featureItems(),
        ...$overrides,
    ]);
}

test('a feature is enabled only when the plan includes it', function () {
    $set = featureSet();

    expect($set->isEnabled('attachments'))->toBeTrue()
        ->and($set->isEnabled('video'))->toBeFalse()
        ->and($set->isEnabled('unknown'))->toBeFalse();
});

test('the limit is the item limit, and null when the plan sets none', function () {
    $set = featureSet();

    expect($set->limit('attachments'))->toBe(15)
        ->and($set->limit('reports'))->toBeNull();
});

test('a capped feature allows amounts up to its limit', function () {
    $set = featureSet();

    expect($set->allows('attachments', 14))->toBeTrue()
        ->and($set->allows('attachments', 15))->toBeTrue()
        ->and($set->allows('attachments', 16))->toBeFalse();
});

test('an unlimited feature allows any amount', function () {
    expect(featureSet()->allows('reports', PHP_INT_MAX))->toBeTrue();
});

test('a locked or unknown feature allows nothing', function () {
    $set = featureSet();

    expect($set->allows('video', 0))->toBeFalse()
        ->and($set->allows('unknown', 0))->toBeFalse();
});

test('read-only does not change what the checks answer', function () {
    $writable = featureSet();
    $readOnly = featureSet(['readOnly' => true]);

    foreach (['attachments', 'reports', 'video', 'unknown'] as $key) {
        expect($readOnly->isEnabled($key))->toBe($writable->isEnabled($key))
            ->and($readOnly->limit($key))->toBe($writable->limit($key));

        foreach ([0, 15, 16] as $amount) {
            expect($readOnly->allows($key, $amount))->toBe($writable->allows($key, $amount));
        }
    }
});

test('the ungated set has no plan and allows every feature without a limit', function () {
    $set = FeatureSet::ungated();

    expect($set->gated)->toBeFalse()
        ->and($set->planName)->toBeNull()
        ->and($set->planCode)->toBeNull()
        ->and($set->subscription)->toBeNull()
        ->and($set->readOnly)->toBeFalse()
        ->and($set->isEnabled('not-a-feature'))->toBeTrue()
        ->and($set->limit('not-a-feature'))->toBeNull()
        ->and($set->allows('not-a-feature', PHP_INT_MAX))->toBeTrue();
});

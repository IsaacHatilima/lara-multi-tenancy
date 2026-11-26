<?php

use App\Actions\Features\SyncFeaturesAction;
use App\Models\Feature;
use App\Models\PlanFeature;

test('it adds a row for each key that has none', function () {
    Feature::factory()->create(['key' => 'test-messaging']);

    $unknownKeys = app(SyncFeaturesAction::class)->handle(['test-messaging', 'test-attachments']);

    expect(Feature::query()->pluck('key')->all())->toEqualCanonicalizing(['test-messaging', 'test-attachments'])
        ->and($unknownKeys)->toBe([]);
});

test('running it twice leaves the same rows', function () {
    $sync = app(SyncFeaturesAction::class);

    $sync->handle(['test-messaging', 'test-attachments']);
    $first = Feature::query()->orderBy('id')->pluck('key', 'id')->all();
    $sync->handle(['test-messaging', 'test-attachments']);

    expect(Feature::query()->orderBy('id')->pluck('key', 'id')->all())->toBe($first)
        ->and($first)->toHaveCount(2);
});

test('it keeps a stored key that is no longer given, with its plan mappings, and returns it', function () {
    $mapping = PlanFeature::factory()->create(['feature_id' => Feature::factory()->create(['key' => 'test-retired'])]);

    $unknownKeys = app(SyncFeaturesAction::class)->handle(['test-messaging']);

    expect($unknownKeys)->toBe(['test-retired'])
        ->and(Feature::query()->where('key', 'test-retired')->exists())->toBeTrue()
        ->and(PlanFeature::query()->whereKey($mapping->id)->exists())->toBeTrue();
});

<?php

use App\Models\Feature;
use App\Repositories\Features\CreateFeaturesRepository;

test('it stores a row for each key', function () {
    app(CreateFeaturesRepository::class)->handle(['test-messaging', 'test-attachments']);

    expect(Feature::query()->pluck('key')->all())->toEqualCanonicalizing(['test-messaging', 'test-attachments'])
        ->and(Feature::query()->whereNull('created_at')->exists())->toBeFalse();
});

test('it leaves an existing row with the same key unchanged', function () {
    $existing = Feature::factory()->create(['key' => 'test-messaging', 'updated_at' => '2026-01-01 00:00:00']);

    app(CreateFeaturesRepository::class)->handle(['test-messaging', 'test-attachments']);

    expect(Feature::query()->where('key', 'test-messaging')->sole())
        ->id->toBe($existing->id)
        ->updated_at->toDateTimeString()->toBe('2026-01-01 00:00:00')
        ->and(Feature::query()->count())->toBe(2);
});

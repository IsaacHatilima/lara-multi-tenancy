<?php

use App\Models\Feature;

test('it adds no rows while the Feature enum has no cases', function () {
    $this->artisan('features:sync')->assertSuccessful();

    expect(Feature::query()->count())->toBe(0);
});

test('it reports a stored key the Feature enum does not have and keeps its row', function () {
    Feature::factory()->create(['key' => 'test-retired']);

    $this->artisan('features:sync')
        ->expectsOutputToContain('test-retired')
        ->assertSuccessful();

    expect(Feature::query()->pluck('key')->all())->toBe(['test-retired']);
});

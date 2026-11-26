<?php

use App\Models\Feature;
use App\Repositories\Features\ListFeatureKeysRepository;

test('it lists the key of every feature row in key order', function () {
    Feature::factory()->create(['key' => 'test-messaging']);
    Feature::factory()->create(['key' => 'test-attachments']);

    expect(app(ListFeatureKeysRepository::class)->handle())
        ->toBe(['test-attachments', 'test-messaging']);
});

<?php

use App\Models\Feature;
use App\Repositories\Features\ListFeatureKeysRepository;

test('it lists the key of every feature row', function () {
    Feature::factory()->create(['key' => 'test-messaging']);
    Feature::factory()->create(['key' => 'test-attachments']);

    expect(app(ListFeatureKeysRepository::class)->handle())
        ->toEqualCanonicalizing(['test-messaging', 'test-attachments']);
});

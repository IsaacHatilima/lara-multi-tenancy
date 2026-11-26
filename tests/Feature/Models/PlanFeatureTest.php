<?php

use App\Models\PlanFeature;
use Illuminate\Database\UniqueConstraintViolationException;

test('a plan maps a feature only once', function () {
    $mapping = PlanFeature::factory()->create();

    PlanFeature::factory()->limit(15)->create([
        'plan_id' => $mapping->plan_id,
        'feature_id' => $mapping->feature_id,
    ]);
})->throws(UniqueConstraintViolationException::class);

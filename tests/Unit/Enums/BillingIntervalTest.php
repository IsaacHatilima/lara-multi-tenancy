<?php

use App\Enums\BillingInterval;
use Tests\TestCase;

// Labels are translated, so the application must be booted.
uses(TestCase::class);

test('the options list each interval with its label', function () {
    expect(BillingInterval::options())->toBe([
        ['value' => 'month', 'label' => 'Monthly'],
        ['value' => 'year', 'label' => 'Yearly'],
    ]);
});

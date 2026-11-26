<?php

declare(strict_types=1);

namespace App\Data\Billing;

use App\Enums\SubscriptionStatus;
use Carbon\CarbonImmutable;

/**
 * A company's subscription as the billing provider reports it; becomes a model once subscriptions have a table.
 */
final readonly class SubscriptionRecord
{
    public function __construct(
        public string $planName,
        public SubscriptionStatus $status,
        public int $amountMinor,
        public string $currency,
        public CarbonImmutable $startedAt,
        public CarbonImmutable $currentPeriodEndsAt,
        public ?string $cardBrand,
        public ?string $cardLastFour,
    ) {
    }
}

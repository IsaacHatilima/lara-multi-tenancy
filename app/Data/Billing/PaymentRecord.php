<?php

declare(strict_types=1);

namespace App\Data\Billing;

use App\Enums\PaymentStatus;
use Carbon\CarbonImmutable;

/**
 * One charge against a company's subscription; becomes a model once payments have a table.
 */
final readonly class PaymentRecord
{
    public function __construct(
        public string $reference,
        public string $description,
        public PaymentStatus $status,
        public int $amountMinor,
        public string $currency,
        public CarbonImmutable $attemptedAt,
    ) {
    }
}

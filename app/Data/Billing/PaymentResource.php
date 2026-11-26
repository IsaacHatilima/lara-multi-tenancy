<?php

declare(strict_types=1);

namespace App\Data\Billing;

use Spatie\LaravelData\Resource;

final class PaymentResource extends Resource
{
    public function __construct(
        public readonly string $reference,
        public readonly string $description,
        public readonly string $status,
        public readonly string $statusLabel,
        public readonly string $amount,
        public readonly string $attemptedAt,
    ) {
    }

    public static function fromRecord(PaymentRecord $payment): self
    {
        return new self(
            reference: $payment->reference,
            description: $payment->description,
            status: $payment->status->value,
            statusLabel: $payment->status->label(),
            amount: Money::format($payment->amountMinor, $payment->currency),
            attemptedAt: $payment->attemptedAt->toIso8601String(),
        );
    }
}

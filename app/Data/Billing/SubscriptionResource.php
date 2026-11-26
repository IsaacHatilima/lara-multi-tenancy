<?php

declare(strict_types=1);

namespace App\Data\Billing;

use Spatie\LaravelData\Resource;

final class SubscriptionResource extends Resource
{
    public function __construct(
        public readonly string $planName,
        public readonly string $status,
        public readonly string $statusLabel,
        public readonly string $price,
        public readonly string $startedAt,
        public readonly string $currentPeriodEndsAt,
        public readonly ?string $paymentMethod,
    ) {
    }

    public static function fromRecord(SubscriptionRecord $subscription): self
    {
        return new self(
            planName: $subscription->planName,
            status: $subscription->status->value,
            statusLabel: $subscription->status->label(),
            price: __(':amount a month', ['amount' => Money::format($subscription->amountMinor, $subscription->currency)]),
            startedAt: $subscription->startedAt->toIso8601String(),
            currentPeriodEndsAt: $subscription->currentPeriodEndsAt->toIso8601String(),
            paymentMethod: self::paymentMethod($subscription),
        );
    }

    private static function paymentMethod(SubscriptionRecord $subscription): ?string
    {
        if ($subscription->cardBrand === null || $subscription->cardLastFour === null) {
            return null;
        }

        return __(':brand ending :digits', ['brand' => $subscription->cardBrand, 'digits' => $subscription->cardLastFour]);
    }
}

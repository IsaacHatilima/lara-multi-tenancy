<?php

declare(strict_types=1);

namespace App\Data\Plans;

use App\Data\Billing\Money;
use App\Models\Plan;
use Spatie\LaravelData\Resource;

final class PlanResource extends Resource
{
    public function __construct(
        public readonly string $id,
        public readonly string $name,
        public readonly string $code,
        public readonly ?string $description,
        public readonly int $priceMinor,
        public readonly string $price,
        public readonly string $currency,
        public readonly string $interval,
        public readonly string $intervalLabel,
        public readonly int $trialDays,
    ) {
    }

    public static function fromModel(Plan $plan): self
    {
        return new self(
            id: $plan->id,
            name: $plan->name,
            code: $plan->code,
            description: $plan->description,
            priceMinor: $plan->price_minor,
            price: Money::format($plan->price_minor, $plan->currency->value),
            currency: $plan->currency->value,
            interval: $plan->interval->value,
            intervalLabel: $plan->interval->label(),
            trialDays: $plan->trial_days,
        );
    }
}

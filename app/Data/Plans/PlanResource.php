<?php

declare(strict_types=1);

namespace App\Data\Plans;

use App\Data\Billing\Money;
use App\Models\Plan;
use Spatie\LaravelData\Resource;

/**
 * A plan as staff see it in the plans list; a custom plan's tenant must already be loaded.
 */
final class PlanResource extends Resource
{
    public function __construct(
        public readonly string $id,
        public readonly string $code,
        public readonly string $name,
        public readonly string $status,
        public readonly string $statusLabel,
        public readonly string $price,
        public readonly string $intervalLabel,
        public readonly ?string $tenantName,
    ) {
    }

    public static function fromModel(Plan $plan): self
    {
        return new self(
            id: $plan->id,
            code: $plan->code,
            name: $plan->name,
            status: $plan->status->value,
            statusLabel: $plan->status->label(),
            price: Money::format($plan->price_minor, $plan->currency),
            intervalLabel: $plan->interval->label(),
            tenantName: $plan->tenant?->name,
        );
    }
}

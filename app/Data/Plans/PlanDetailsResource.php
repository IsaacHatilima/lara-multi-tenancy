<?php

declare(strict_types=1);

namespace App\Data\Plans;

use App\Models\Plan;
use Spatie\LaravelData\Resource;

/**
 * A plan as staff edit it, with every feature row and whether the plan includes it; a custom plan's tenant must already be loaded.
 */
final class PlanDetailsResource extends Resource
{
    /**
     * @param  list<PlanFeatureResource>  $features
     */
    public function __construct(
        public readonly string $id,
        public readonly string $code,
        public readonly string $name,
        public readonly ?string $description,
        public readonly int $sortOrder,
        public readonly string $status,
        public readonly string $statusLabel,
        public readonly ?string $tenantName,
        public readonly array $features,
    ) {
    }

    /**
     * @param  list<PlanFeatureResource>  $features
     */
    public static function fromModel(Plan $plan, array $features): self
    {
        return new self(
            id: $plan->id,
            code: $plan->code,
            name: $plan->name,
            description: $plan->description,
            sortOrder: $plan->sort_order,
            status: $plan->status->value,
            statusLabel: $plan->status->label(),
            tenantName: $plan->tenant?->name,
            features: $features,
        );
    }
}

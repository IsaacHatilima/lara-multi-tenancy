<?php

declare(strict_types=1);

namespace App\Data\Features;

use App\Models\Plan;
use Spatie\LaravelData\Resource;

/**
 * A plan that includes a feature, as the Features page names it; a custom plan's tenant must already be loaded.
 */
final class FeaturePlanResource extends Resource
{
    public function __construct(
        public readonly string $id,
        public readonly string $code,
        public readonly string $name,
        public readonly ?string $tenantName,
    ) {
    }

    public static function fromModel(Plan $plan): self
    {
        return new self(
            id: $plan->id,
            code: $plan->code,
            name: $plan->name,
            tenantName: $plan->tenant?->name,
        );
    }
}

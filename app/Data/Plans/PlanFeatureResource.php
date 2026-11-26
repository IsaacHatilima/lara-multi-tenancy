<?php

declare(strict_types=1);

namespace App\Data\Plans;

use App\Models\Feature;
use Spatie\LaravelData\Resource;

/**
 * A feature as it stands on one plan: whether the plan includes it, and its limit, where null means unlimited.
 */
final class PlanFeatureResource extends Resource
{
    public function __construct(
        public readonly int $id,
        public readonly string $key,
        public readonly string $name,
        public readonly ?string $limitUnitLabel,
        public readonly bool $included,
        public readonly ?int $limit,
    ) {
    }

    /**
     * @param  array<int, int|null>  $planLimits  the features the plan includes, keyed by feature id
     */
    public static function fromModel(Feature $feature, array $planLimits): self
    {
        return new self(
            id: $feature->id,
            key: $feature->key,
            name: $feature->name,
            limitUnitLabel: $feature->limit_unit?->label(),
            included: array_key_exists($feature->id, $planLimits),
            limit: $planLimits[$feature->id] ?? null,
        );
    }
}

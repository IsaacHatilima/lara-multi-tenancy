<?php

declare(strict_types=1);

namespace App\Data\Plans;

use App\Models\PlanFeature;
use Spatie\LaravelData\Resource;

/**
 * A feature a plan includes, with its limit on that plan, where null means unlimited.
 */
final class PlanFeatureResource extends Resource
{
    public function __construct(
        public readonly int $id,
        public readonly string $key,
        public readonly string $name,
        public readonly ?string $limitUnitLabel,
        public readonly ?int $limit,
    ) {
    }

    /**
     * The id is the feature's, which the plan page's routes take.
     */
    public static function fromModel(PlanFeature $planFeature): self
    {
        return new self(
            id: $planFeature->feature->id,
            key: $planFeature->feature->key,
            name: $planFeature->feature->name,
            limitUnitLabel: $planFeature->feature->limit_unit?->label(),
            limit: $planFeature->limit,
        );
    }
}

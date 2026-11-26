<?php

declare(strict_types=1);

namespace App\Data\Features;

use App\Enums\Feature as FeatureKey;
use App\Models\Feature;
use App\Models\Plan;
use Spatie\LaravelData\Resource;

/**
 * A feature row with its limit unit and the plans that include it; the plans and their tenants must already be loaded.
 */
final class FeatureResource extends Resource
{
    /**
     * @param  list<FeaturePlanResource>  $plans
     */
    public function __construct(
        public readonly string $key,
        public readonly ?string $unitLabel,
        public readonly array $plans,
    ) {
    }

    public static function fromModel(Feature $feature): self
    {
        return new self(
            key: $feature->key,
            unitLabel: FeatureKey::limitUnitOf($feature->key)?->label(),
            plans: array_values($feature->plans->map(
                fn (Plan $plan): FeaturePlanResource => FeaturePlanResource::fromModel($plan),
            )->all()),
        );
    }
}

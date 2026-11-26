<?php

declare(strict_types=1);

namespace App\Data\Plans;

use App\Enums\Feature;
use Spatie\LaravelData\Resource;

/**
 * A feature as it stands on one plan: whether the plan includes it, and its limit, where null means unlimited.
 */
final class PlanFeatureResource extends Resource
{
    public function __construct(
        public readonly string $key,
        public readonly ?string $unitLabel,
        public readonly bool $included,
        public readonly ?int $limit,
    ) {
    }

    /**
     * @param  array<string, int|null>  $planLimits  the features the plan includes, keyed by feature key
     */
    public static function fromKey(string $key, array $planLimits): self
    {
        return new self(
            key: $key,
            unitLabel: Feature::limitUnitOf($key)?->label(),
            included: array_key_exists($key, $planLimits),
            limit: $planLimits[$key] ?? null,
        );
    }
}

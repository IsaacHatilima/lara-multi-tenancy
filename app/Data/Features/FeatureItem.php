<?php

declare(strict_types=1);

namespace App\Data\Features;

use App\Enums\LimitUnit;

/**
 * One feature as a tenant's plan grants it. An empty limit means unlimited; a locked feature lists the plans that include it.
 */
final readonly class FeatureItem
{
    /**
     * @param  list<string>  $availableOn
     */
    public function __construct(
        public bool $enabled,
        public ?int $limit,
        public ?LimitUnit $unit,
        public array $availableOn,
    ) {
    }
}

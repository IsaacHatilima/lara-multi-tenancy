<?php

declare(strict_types=1);

namespace App\Data\Features;

use App\Models\Feature;
use Spatie\LaravelData\Resource;

final class FeatureResource extends Resource
{
    public function __construct(
        public readonly int $id,
        public readonly string $key,
        public readonly string $name,
        public readonly ?string $description,
        public readonly ?string $limitUnit,
        public readonly ?string $limitUnitLabel,
    ) {
    }

    public static function fromModel(Feature $feature): self
    {
        return new self(
            id: $feature->id,
            key: $feature->key,
            name: $feature->name,
            description: $feature->description,
            limitUnit: $feature->limit_unit?->value,
            limitUnitLabel: $feature->limit_unit?->label(),
        );
    }
}

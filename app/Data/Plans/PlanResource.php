<?php

declare(strict_types=1);

namespace App\Data\Plans;

use App\Models\Plan;
use Spatie\LaravelData\Resource;

final class PlanResource extends Resource
{
    public function __construct(
        public readonly string $id,
        public readonly string $name,
        public readonly string $code,
        public readonly ?string $description,
    ) {
    }

    public static function fromModel(Plan $plan): self
    {
        return new self(
            id: $plan->id,
            name: $plan->name,
            code: $plan->code,
            description: $plan->description,
        );
    }
}

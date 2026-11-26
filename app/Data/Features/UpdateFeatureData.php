<?php

declare(strict_types=1);

namespace App\Data\Features;

use App\Enums\LimitUnit;
use App\Http\Requests\Features\UpdateFeatureRequest;
use Spatie\LaravelData\Data;

final class UpdateFeatureData extends Data
{
    public function __construct(
        public readonly string $name,
        public readonly ?string $description,
        public readonly ?LimitUnit $limitUnit,
    ) {
    }

    public static function fromRequest(UpdateFeatureRequest $request): self
    {
        $description = trim($request->string('description')->value());

        return new self(
            name: trim($request->string('name')->value()),
            description: $description === '' ? null : $description,
            limitUnit: $request->enum('limit_unit', LimitUnit::class),
        );
    }
}

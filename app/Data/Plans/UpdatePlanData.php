<?php

declare(strict_types=1);

namespace App\Data\Plans;

use App\Http\Requests\Plans\UpdatePlanRequest;
use Spatie\LaravelData\Data;

/**
 * A plan's own fields and the features it includes, as staff saved them on the edit page.
 */
final class UpdatePlanData extends Data
{
    /**
     * @param  array<string, int|null>  $featureLimits  the features the plan includes, keyed by feature key, each with its limit or null for unlimited
     */
    public function __construct(
        public readonly string $name,
        public readonly ?string $description,
        public readonly int $sortOrder,
        public readonly array $featureLimits,
    ) {
    }

    public static function fromRequest(UpdatePlanRequest $request): self
    {
        $description = trim($request->string('description')->value());

        return new self(
            name: trim($request->string('name')->value()),
            description: $description === '' ? null : $description,
            sortOrder: $request->integer('sort_order'),
            featureLimits: $request->collect('features')
                ->mapWithKeys(fn (array $feature): array => [
                    $feature['key'] => isset($feature['limit']) ? (int) $feature['limit'] : null,
                ])
                ->all(),
        );
    }
}

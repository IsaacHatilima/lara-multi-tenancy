<?php

declare(strict_types=1);

namespace App\Data\Plans;

use App\Http\Requests\Plans\UpdatePlanRequest;
use Spatie\LaravelData\Data;

final class UpdatePlanData extends Data
{
    /**
     * @param  array<int, int|null>  $featureLimits  the features the plan includes, keyed by feature id, each with its limit or null for unlimited
     */
    public function __construct(
        public readonly string $name,
        public readonly string $code,
        public readonly ?string $description,
        public readonly array $featureLimits,
    ) {
    }

    public static function fromRequest(UpdatePlanRequest $request): self
    {
        $description = trim($request->string('description')->value());

        return new self(
            name: trim($request->string('name')->value()),
            code: trim($request->string('code')->value()),
            description: $description === '' ? null : $description,
            featureLimits: $request->collect('features')
                ->mapWithKeys(fn (array $feature): array => [
                    (int) $feature['id'] => isset($feature['limit']) ? (int) $feature['limit'] : null,
                ])
                ->all(),
        );
    }
}

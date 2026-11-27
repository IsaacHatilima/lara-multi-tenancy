<?php

declare(strict_types=1);

namespace App\Data\Plans;

use App\Http\Requests\Plans\AddPlanFeatureRequest;
use Spatie\LaravelData\Data;

final class AddPlanFeatureData extends Data
{
    /**
     * @param  int|null  $limit  null means unlimited
     */
    public function __construct(
        public readonly int $featureId,
        public readonly ?int $limit,
    ) {
    }

    public static function fromRequest(AddPlanFeatureRequest $request): self
    {
        return new self(
            featureId: $request->integer('feature_id'),
            limit: $request->filled('limit') ? $request->integer('limit') : null,
        );
    }
}

<?php

declare(strict_types=1);

namespace App\Data\Plans;

use App\Http\Requests\Plans\UpdatePlanFeatureRequest;
use Spatie\LaravelData\Data;

final class UpdatePlanFeatureData extends Data
{
    /**
     * @param  int|null  $limit  null means unlimited
     */
    public function __construct(
        public readonly ?int $limit,
    ) {
    }

    public static function fromRequest(UpdatePlanFeatureRequest $request): self
    {
        return new self(limit: $request->filled('limit') ? $request->integer('limit') : null);
    }
}

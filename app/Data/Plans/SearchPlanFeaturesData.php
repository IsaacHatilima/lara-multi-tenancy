<?php

declare(strict_types=1);

namespace App\Data\Plans;

use App\Http\Requests\Plans\SearchPlanFeaturesRequest;
use Spatie\LaravelData\Data;

final class SearchPlanFeaturesData extends Data
{
    public function __construct(
        public readonly ?string $search,
    ) {
    }

    public static function fromRequest(SearchPlanFeaturesRequest $request): self
    {
        $search = trim($request->string('search')->value());

        return new self(search: $search === '' ? null : $search);
    }
}

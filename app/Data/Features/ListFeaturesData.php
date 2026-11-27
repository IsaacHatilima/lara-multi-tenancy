<?php

declare(strict_types=1);

namespace App\Data\Features;

use App\Http\Requests\Features\ListFeaturesRequest;
use Spatie\LaravelData\Data;

final class ListFeaturesData extends Data
{
    public function __construct(
        public readonly ?string $search,
    ) {
    }

    public static function fromRequest(ListFeaturesRequest $request): self
    {
        $search = trim($request->string('filter.search')->value());

        return new self(search: $search === '' ? null : $search);
    }
}

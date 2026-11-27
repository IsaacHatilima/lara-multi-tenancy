<?php

declare(strict_types=1);

namespace App\Data\Plans;

use App\Http\Requests\Plans\ListPlansRequest;
use Spatie\LaravelData\Data;

final class ListPlansData extends Data
{
    public function __construct(
        public readonly ?string $search,
    ) {
    }

    public static function fromRequest(ListPlansRequest $request): self
    {
        $search = trim($request->string('filter.search')->value());

        return new self(search: $search === '' ? null : $search);
    }
}

<?php

declare(strict_types=1);

namespace App\Data\Tenants;

use App\Http\Requests\Tenants\ListTenantsRequest;
use Spatie\LaravelData\Data;

final class ListTenantsData extends Data
{
    public function __construct(
        public readonly ?string $search,
    ) {
    }

    public static function fromRequest(ListTenantsRequest $request): self
    {
        $search = trim($request->string('search')->value());

        return new self(search: $search === '' ? null : $search);
    }
}

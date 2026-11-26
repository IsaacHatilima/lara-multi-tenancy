<?php

declare(strict_types=1);

namespace App\Data\Plans;

use App\Http\Requests\Plans\CreatePlanRequest;
use Spatie\LaravelData\Data;

final class CreatePlanData extends Data
{
    public function __construct(
        public readonly string $name,
        public readonly string $code,
        public readonly ?string $description,
    ) {
    }

    public static function fromRequest(CreatePlanRequest $request): self
    {
        $description = trim($request->string('description')->value());

        return new self(
            name: trim($request->string('name')->value()),
            code: trim($request->string('code')->value()),
            description: $description === '' ? null : $description,
        );
    }
}

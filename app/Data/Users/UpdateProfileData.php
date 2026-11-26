<?php

declare(strict_types=1);

namespace App\Data\Users;

use App\Http\Requests\Settings\ProfileUpdateRequest;
use Spatie\LaravelData\Data;

final class UpdateProfileData extends Data
{
    public function __construct(
        public readonly string $name,
        public readonly string $email,
    ) {
    }

    public static function fromRequest(ProfileUpdateRequest $request): self
    {
        return new self(
            name: trim($request->string('name')->value()),
            email: UserEmail::normalize($request->string('email')->value()),
        );
    }
}

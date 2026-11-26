<?php

declare(strict_types=1);

namespace App\Data\Users;

use App\Http\Requests\Settings\PasswordUpdateRequest;
use Spatie\LaravelData\Data;

final class UpdatePasswordData extends Data
{
    public function __construct(
        public readonly string $password,
    ) {
    }

    public static function fromRequest(PasswordUpdateRequest $request): self
    {
        return new self(password: $request->string('password')->value());
    }

    /**
     * Build the data from Fortify's validated password reset input.
     *
     * @param  array{password: string}  $input
     */
    public static function fromValidated(array $input): self
    {
        return new self(password: $input['password']);
    }
}

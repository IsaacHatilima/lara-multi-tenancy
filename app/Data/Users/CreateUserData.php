<?php

declare(strict_types=1);

namespace App\Data\Users;

use Spatie\LaravelData\Data;

final class CreateUserData extends Data
{
    public function __construct(
        public readonly string $name,
        public readonly string $email,
        public readonly string $password,
    ) {
    }

    /**
     * Build the data from Fortify's validated registration input.
     *
     * @param  array{name: string, email: string, password: string}  $input
     */
    public static function fromValidated(array $input): self
    {
        return new self(
            name: trim($input['name']),
            email: UserEmail::normalize($input['email']),
            password: $input['password'],
        );
    }
}

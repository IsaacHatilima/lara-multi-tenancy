<?php

declare(strict_types=1);

namespace App\Data\Users;

use Spatie\LaravelData\Data;

final class CreateUserData extends Data
{
    public function __construct(
        public readonly string $firstName,
        public readonly string $lastName,
        public readonly string $email,
        public readonly string $password,
    ) {
    }

    /**
     * Build the data from Fortify's validated registration input.
     *
     * @param  array{first_name: string, last_name: string, email: string, password: string}  $input
     */
    public static function fromValidated(array $input): self
    {
        return new self(
            firstName: trim($input['first_name']),
            lastName: trim($input['last_name']),
            email: UserEmail::normalize($input['email']),
            password: $input['password'],
        );
    }
}

<?php

declare(strict_types=1);

namespace App\Actions\Users;

use App\Data\Users\CreateUserData;
use App\Models\User;
use App\Repositories\Users\CreateUserRepository;
use App\Repositories\Users\UserEmailTakenRepository;
use Illuminate\Validation\ValidationException;

final readonly class CreateUserAction
{
    public function __construct(
        private UserEmailTakenRepository $emailTaken,
        private CreateUserRepository $createUser,
    ) {
    }

    public function handle(CreateUserData $data): User
    {
        if ($this->emailTaken->handle($data->email)) {
            throw ValidationException::withMessages([
                'email' => __('The email has already been taken.'),
            ]);
        }

        return $this->createUser->handle($data);
    }
}

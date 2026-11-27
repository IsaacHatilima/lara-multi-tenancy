<?php

declare(strict_types=1);

namespace App\Actions\Users;

use App\Data\Users\CreateUserData;
use App\Models\User;
use App\Repositories\Users\UserRepository;
use Illuminate\Validation\ValidationException;

final readonly class CreateUserAction
{
    public function __construct(
        private UserRepository $users,
    ) {
    }

    public function handle(CreateUserData $data): User
    {
        if ($this->users->emailTaken($data->email)) {
            throw ValidationException::withMessages([
                'email' => __('The email has already been taken.'),
            ]);
        }

        return $this->users->create($data);
    }
}

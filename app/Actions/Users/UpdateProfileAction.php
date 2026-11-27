<?php

declare(strict_types=1);

namespace App\Actions\Users;

use App\Data\Users\UpdateProfileData;
use App\Models\User;
use App\Repositories\Users\UserRepository;
use Illuminate\Validation\ValidationException;

final readonly class UpdateProfileAction
{
    public function __construct(
        private UserRepository $users,
    ) {
    }

    public function handle(User $user, UpdateProfileData $data): User
    {
        if ($this->users->emailTaken($data->email, $user->id)) {
            throw ValidationException::withMessages([
                'email' => __('The email has already been taken.'),
            ]);
        }

        return $this->users->updateProfile($user, $data);
    }
}

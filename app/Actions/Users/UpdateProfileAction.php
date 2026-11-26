<?php

declare(strict_types=1);

namespace App\Actions\Users;

use App\Data\Users\UpdateProfileData;
use App\Models\User;
use App\Repositories\Users\UpdateProfileRepository;
use App\Repositories\Users\UserEmailTakenRepository;
use Illuminate\Validation\ValidationException;

final readonly class UpdateProfileAction
{
    public function __construct(
        private UserEmailTakenRepository $emailTaken,
        private UpdateProfileRepository $updateProfile,
    ) {
    }

    public function handle(User $user, UpdateProfileData $data): User
    {
        if ($this->emailTaken->handle($data->email, except: $user)) {
            throw ValidationException::withMessages([
                'email' => __('The email has already been taken.'),
            ]);
        }

        return $this->updateProfile->handle(
            user: $user,
            name: $data->name,
            email: $data->email,
            unverifyEmail: $user->email !== $data->email,
        );
    }
}

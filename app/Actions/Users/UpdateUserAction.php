<?php

declare(strict_types=1);

namespace App\Actions\Users;

use App\Data\Users\UpdateUserData;
use App\Models\User;
use App\Repositories\Users\UserRepository;
use Illuminate\Validation\ValidationException;
use Spatie\LaravelData\Optional;

final readonly class UpdateUserAction
{
    public function __construct(
        private UserRepository $users,
    ) {
    }

    public function handle(User $user, UpdateUserData $data): User
    {
        if (! $data->email instanceof Optional && $this->users->emailTaken($data->email, $user->id)) {
            throw ValidationException::withMessages([
                'email' => __('The email has already been taken.'),
            ]);
        }

        return $this->users->update($user, $data);
    }
}

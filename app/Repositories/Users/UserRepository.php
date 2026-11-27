<?php

declare(strict_types=1);

namespace App\Repositories\Users;

use App\Data\Users\CreateUserData;
use App\Data\Users\UpdateProfileData;
use App\Models\User;

final readonly class UserRepository
{
    public function create(CreateUserData $data): User
    {
        $user = new User;

        $user->name = $data->name;
        $user->email = $data->email;
        $user->password = $data->password;
        $user->save();

        return $user;
    }

    /**
     * A changed email address has to be verified again.
     */
    public function updateProfile(User $user, UpdateProfileData $data): User
    {
        $user->name = $data->name;
        $user->email = $data->email;

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        return $user;
    }

    public function updatePassword(User $user, string $password): void
    {
        $user->password = $password;
        $user->save();
    }

    public function delete(User $user): void
    {
        $user->delete();
    }

    /**
     * Whether another user already has this email; the user with `$userId` doesn't count.
     */
    public function emailTaken(string $email, ?int $userId = null): bool
    {
        $query = User::query()->where('email', $email);

        if ($userId !== null) {
            $query->whereKeyNot($userId);
        }

        return $query->exists();
    }
}

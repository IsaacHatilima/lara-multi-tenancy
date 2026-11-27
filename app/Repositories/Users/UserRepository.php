<?php

declare(strict_types=1);

namespace App\Repositories\Users;

use App\Data\Users\CreateUserData;
use App\Data\Users\UpdateProfileData;
use App\Models\Profile;
use App\Models\User;

final readonly class UserRepository
{
    /**
     * Creates the user with their profile, together.
     */
    public function create(CreateUserData $data): User
    {
        $user = new User();

        return $user->getConnection()->transaction(function () use ($user, $data): User {
            $user->email = $data->email;
            $user->password = $data->password;
            $user->save();

            $profile = new Profile();
            $profile->user_id = $user->id;
            $profile->first_name = $data->firstName;
            $profile->last_name = $data->lastName;
            $profile->save();

            return $user->setRelation('profile', $profile);
        });
    }

    /**
     * Saves the email and the profile's names together. A changed email address has to be verified again.
     */
    public function updateProfile(User $user, UpdateProfileData $data): User
    {
        return $user->getConnection()->transaction(function () use ($user, $data): User {
            $user->email = $data->email;

            if ($user->isDirty('email')) {
                $user->email_verified_at = null;
            }

            $user->save();

            $profile = $user->loadMissing('profile')->profile;
            $profile->first_name = $data->firstName;
            $profile->last_name = $data->lastName;
            $profile->save();

            return $user;
        });
    }

    /**
     * The user with the profile columns the app shows.
     */
    public function loadProfile(User $user): User
    {
        return $user->load(['profile' => fn ($query) => $query->select(['id', 'user_id', 'first_name', 'last_name'])]);
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
    public function emailTaken(string $email, ?string $userId = null): bool
    {
        $query = User::query()->where('email', $email);

        if ($userId !== null) {
            $query->whereKeyNot($userId);
        }

        return $query->exists();
    }
}

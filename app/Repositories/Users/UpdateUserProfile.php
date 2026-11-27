<?php

declare(strict_types=1);

namespace App\Repositories\Users;

use App\Data\Users\UpdateProfileData;
use App\Models\User;

final readonly class UpdateUserProfile
{
    /**
     * Saves the email and the profile's names together. A changed email address has to be verified again.
     */
    public function handle(User $user, UpdateProfileData $data): User
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
}

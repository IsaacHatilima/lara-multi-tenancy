<?php

declare(strict_types=1);

namespace App\Repositories\Users;

use App\Data\Users\CreateUserData;
use App\Models\Profile;
use App\Models\User;

final readonly class CreateUser
{
    /**
     * Creates the user with their profile, together.
     */
    public function handle(CreateUserData $data): User
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
}

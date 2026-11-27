<?php

declare(strict_types=1);

namespace App\Repositories\Users;

use App\Data\Users\CreateUserData;
use App\Models\User;
use App\Repositories\Profiles\ProfileRepository;

final readonly class CreateUser
{
    public function __construct(
        private ProfileRepository $profiles,
    ) {
    }

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

            return $user->setRelation('profile', $this->profiles->create($user, $data));
        });
    }
}

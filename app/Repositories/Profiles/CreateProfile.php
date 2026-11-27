<?php

declare(strict_types=1);

namespace App\Repositories\Profiles;

use App\Data\Users\CreateUserData;
use App\Models\Profile;
use App\Models\User;

final readonly class CreateProfile
{
    public function handle(User $user, CreateUserData $data): Profile
    {
        $profile = $user->profile()->make();

        $profile->first_name = $data->firstName;
        $profile->last_name = $data->lastName;
        $profile->save();

        return $profile;
    }
}

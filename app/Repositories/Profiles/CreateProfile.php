<?php

declare(strict_types=1);

namespace App\Repositories\Profiles;

use App\Models\Profile;
use App\Models\User;

final readonly class CreateProfile
{
    public function handle(User $user, string $firstName, string $lastName): Profile
    {
        $profile = new Profile();

        $profile->user_id = $user->id;
        $profile->first_name = $firstName;
        $profile->last_name = $lastName;
        $profile->save();

        return $profile;
    }
}

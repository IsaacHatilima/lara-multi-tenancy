<?php

declare(strict_types=1);

namespace App\Repositories\Profiles;

use App\Data\Users\UpdateProfileData;
use App\Models\Profile;

final readonly class UpdateProfile
{
    public function handle(Profile $profile, UpdateProfileData $data): Profile
    {
        $profile->first_name = $data->firstName;
        $profile->last_name = $data->lastName;
        $profile->save();

        return $profile;
    }
}

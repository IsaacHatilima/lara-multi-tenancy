<?php

declare(strict_types=1);

namespace App\Repositories\Profiles;

use App\Data\Users\UpdateProfileData;
use App\Models\Profile;

/**
 * Entry file for all Profile persistence. Each operation lives in its own class in this folder; this class composes
 * them so an action needs one import and one constructor parameter instead of one per operation.
 */
final readonly class ProfileRepository
{
    public function __construct(
        private UpdateProfile $updateProfile,
    ) {
    }

    public function update(Profile $profile, UpdateProfileData $data): Profile
    {
        return $this->updateProfile->handle($profile, $data);
    }
}

<?php

declare(strict_types=1);

namespace App\Repositories\Profiles;

use App\Data\Users\CreateUserData;
use App\Data\Users\UpdateProfileData;
use App\Models\Profile;
use App\Models\User;

/**
 * Entry file for all Profile persistence. Each operation lives in its own class in this folder; this class composes
 * them so an action needs one import and one constructor parameter instead of one per operation.
 */
final readonly class ProfileRepository
{
    public function __construct(
        private CreateProfile $createProfile,
        private UpdateProfile $updateProfile,
    ) {
    }

    public function create(User $user, CreateUserData $data): Profile
    {
        return $this->createProfile->handle($user, $data);
    }

    public function update(Profile $profile, UpdateProfileData $data): Profile
    {
        return $this->updateProfile->handle($profile, $data);
    }
}

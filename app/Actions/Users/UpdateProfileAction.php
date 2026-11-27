<?php

declare(strict_types=1);

namespace App\Actions\Users;

use App\Data\Users\UpdateProfileData;
use App\Models\Profile;
use App\Models\User;
use App\Repositories\Profiles\ProfileRepository;
use App\Repositories\Users\UserRepository;

final readonly class UpdateProfileAction
{
    public function __construct(
        private UserRepository $users,
        private ProfileRepository $profiles,
    ) {
    }

    public function handle(User $user, UpdateProfileData $data): Profile
    {
        return $this->profiles->update($this->users->loadProfile($user)->profile, $data);
    }
}

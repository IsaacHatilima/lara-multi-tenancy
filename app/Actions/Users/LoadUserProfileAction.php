<?php

declare(strict_types=1);

namespace App\Actions\Users;

use App\Models\User;
use App\Repositories\Users\UserRepository;

final readonly class LoadUserProfileAction
{
    public function __construct(
        private UserRepository $users,
    ) {
    }

    public function handle(User $user): User
    {
        return $this->users->loadProfile($user);
    }
}

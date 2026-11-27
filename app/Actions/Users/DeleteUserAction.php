<?php

declare(strict_types=1);

namespace App\Actions\Users;

use App\Models\User;
use App\Repositories\Users\UserRepository;

final readonly class DeleteUserAction
{
    public function __construct(
        private UserRepository $users,
    ) {
    }

    public function handle(User $user): void
    {
        $this->users->delete($user);
    }
}

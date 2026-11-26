<?php

declare(strict_types=1);

namespace App\Repositories\Users;

use App\Models\User;

final readonly class DeleteUserRepository
{
    public function handle(User $user): void
    {
        $user->delete();
    }
}

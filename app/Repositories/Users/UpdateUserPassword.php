<?php

declare(strict_types=1);

namespace App\Repositories\Users;

use App\Models\User;

final readonly class UpdateUserPassword
{
    public function handle(User $user, string $password): void
    {
        $user->password = $password;
        $user->save();
    }
}

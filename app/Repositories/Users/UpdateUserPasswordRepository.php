<?php

declare(strict_types=1);

namespace App\Repositories\Users;

use App\Models\User;

final readonly class UpdateUserPasswordRepository
{
    public function handle(User $user, string $password): void
    {
        $user->forceFill(['password' => $password])->save();
    }
}

<?php

declare(strict_types=1);

namespace App\Repositories\Users;

use App\Models\User;

final readonly class UpdateProfileRepository
{
    public function handle(User $user, string $name, string $email, bool $unverifyEmail): User
    {
        $user->fill(['name' => $name, 'email' => $email]);

        if ($unverifyEmail) {
            $user->email_verified_at = null;
        }

        $user->save();

        return $user;
    }
}

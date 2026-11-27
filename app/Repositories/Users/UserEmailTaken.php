<?php

declare(strict_types=1);

namespace App\Repositories\Users;

use App\Models\User;

final readonly class UserEmailTaken
{
    /**
     * Whether another user already has this email; the user with `$userId` doesn't count.
     */
    public function handle(string $email, ?string $userId = null): bool
    {
        $query = User::query();
        $query->where('email', $email);

        if ($userId !== null) {
            $query->whereKeyNot($userId);
        }

        return $query->exists();
    }
}

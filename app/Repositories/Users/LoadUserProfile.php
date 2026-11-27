<?php

declare(strict_types=1);

namespace App\Repositories\Users;

use App\Models\User;

final readonly class LoadUserProfile
{
    /**
     * The user with the profile columns the app shows.
     */
    public function handle(User $user): User
    {
        return $user->load(['profile' => fn ($query) => $query->select(['id', 'user_id', 'first_name', 'last_name'])]);
    }
}

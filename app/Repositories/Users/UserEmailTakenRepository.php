<?php

declare(strict_types=1);

namespace App\Repositories\Users;

use App\Models\User;

final readonly class UserEmailTakenRepository
{
    public function handle(string $email, ?User $except = null): bool
    {
        return User::query()
            ->where('email', $email)
            ->when($except !== null, fn ($query) => $query->whereKeyNot($except?->getKey()))
            ->exists();
    }
}

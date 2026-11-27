<?php

declare(strict_types=1);

namespace App\Repositories\Users;

use App\Data\Users\UpdateUserData;
use App\Models\User;
use Spatie\LaravelData\Optional;

final readonly class UpdateUser
{
    /**
     * Writes only the users columns the data carries. A changed email address has to be verified again.
     */
    public function handle(User $user, UpdateUserData $data): User
    {
        if (! $data->email instanceof Optional) {
            $user->email = $data->email;
        }

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        return $user;
    }
}

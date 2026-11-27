<?php

declare(strict_types=1);

namespace App\Actions\Users;

use App\Data\Users\UpdatePasswordData;
use App\Models\User;
use App\Repositories\Users\UserRepository;

final readonly class UpdateUserPasswordAction
{
    public function __construct(
        private UserRepository $users,
    ) {
    }

    public function handle(User $user, UpdatePasswordData $data): void
    {
        $this->users->updatePassword($user, $data->password);
    }
}

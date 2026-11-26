<?php

declare(strict_types=1);

namespace App\Actions\Users;

use App\Models\User;
use App\Repositories\Users\DeleteUserRepository;

final readonly class DeleteUserAction
{
    public function __construct(
        private DeleteUserRepository $deleteUser,
    ) {
    }

    public function handle(User $user): void
    {
        $this->deleteUser->handle($user);
    }
}

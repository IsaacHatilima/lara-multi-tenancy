<?php

declare(strict_types=1);

namespace App\Actions\Users;

use App\Data\Users\UpdatePasswordData;
use App\Models\User;
use App\Repositories\Users\UpdateUserPasswordRepository;

final readonly class UpdateUserPasswordAction
{
    public function __construct(
        private UpdateUserPasswordRepository $updatePassword,
    ) {
    }

    public function handle(User $user, UpdatePasswordData $data): void
    {
        $this->updatePassword->handle($user, $data->password);
    }
}

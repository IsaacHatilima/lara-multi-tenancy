<?php

declare(strict_types=1);

namespace App\Repositories\Users;

use App\Data\Users\CreateUserData;
use App\Data\Users\UpdateProfileData;
use App\Models\User;

/**
 * Entry file for all User persistence. Each operation lives in its own class in this folder; this class composes
 * them so an action needs one import and one constructor parameter instead of one per operation.
 */
final readonly class UserRepository
{
    public function __construct(
        private CreateUser $createUser,
        private UpdateUserProfile $updateUserProfile,
        private LoadUserProfile $loadUserProfile,
        private UpdateUserPassword $updateUserPassword,
        private DeleteUser $deleteUser,
        private UserEmailTaken $userEmailTaken,
    ) {
    }

    public function create(CreateUserData $data): User
    {
        return $this->createUser->handle($data);
    }

    public function updateProfile(User $user, UpdateProfileData $data): User
    {
        return $this->updateUserProfile->handle($user, $data);
    }

    public function loadProfile(User $user): User
    {
        return $this->loadUserProfile->handle($user);
    }

    public function updatePassword(User $user, string $password): void
    {
        $this->updateUserPassword->handle($user, $password);
    }

    public function delete(User $user): void
    {
        $this->deleteUser->handle($user);
    }

    public function emailTaken(string $email, ?string $userId = null): bool
    {
        return $this->userEmailTaken->handle($email, $userId);
    }
}

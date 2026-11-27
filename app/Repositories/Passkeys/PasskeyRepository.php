<?php

declare(strict_types=1);

namespace App\Repositories\Passkeys;

use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Laravel\Passkeys\Passkey;

/**
 * Entry file for all Passkey persistence. Each operation lives in its own class in this folder; this class composes
 * them so an action needs one import and one constructor parameter instead of one per operation.
 */
final readonly class PasskeyRepository
{
    public function __construct(
        private ListUserPasskeys $listUserPasskeys,
    ) {
    }

    /**
     * @return Collection<int, Passkey>
     */
    public function listForUser(User $user): Collection
    {
        return $this->listUserPasskeys->handle($user);
    }
}

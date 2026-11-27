<?php

declare(strict_types=1);

namespace App\Actions\Users;

use App\Data\Users\PasskeyResource;
use App\Models\User;
use App\Repositories\Passkeys\PasskeyRepository;
use Laravel\Passkeys\Passkey;

final readonly class ListPasskeysAction
{
    public function __construct(
        private PasskeyRepository $passkeys,
    ) {
    }

    /**
     * @return list<PasskeyResource>
     */
    public function handle(User $user): array
    {
        return array_values($this->passkeys->listForUser($user)
            ->map(fn (Passkey $passkey): PasskeyResource => PasskeyResource::fromModel($passkey))
            ->all());
    }
}

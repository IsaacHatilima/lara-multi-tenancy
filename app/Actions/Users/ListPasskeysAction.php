<?php

declare(strict_types=1);

namespace App\Actions\Users;

use App\Data\Users\PasskeyResource;
use App\Models\User;
use App\Repositories\Users\ListPasskeysRepository;
use Laravel\Passkeys\Passkey;

final readonly class ListPasskeysAction
{
    public function __construct(
        private ListPasskeysRepository $listPasskeys,
    ) {
    }

    /**
     * @return list<PasskeyResource>
     */
    public function handle(User $user): array
    {
        return array_values($this->listPasskeys->handle($user)
            ->map(fn (Passkey $passkey): PasskeyResource => PasskeyResource::fromModel($passkey))
            ->all());
    }
}

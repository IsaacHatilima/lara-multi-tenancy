<?php

declare(strict_types=1);

namespace App\Data\Users;

use Laravel\Passkeys\Passkey;
use Spatie\LaravelData\Resource;

final class PasskeyResource extends Resource
{
    public function __construct(
        public readonly int $id,
        public readonly string $name,
        public readonly ?string $authenticator,
        public readonly string $createdAtDiff,
        public readonly ?string $lastUsedAtDiff,
    ) {
    }

    public static function fromModel(Passkey $passkey): self
    {
        return new self(
            id: $passkey->id,
            name: $passkey->name,
            authenticator: $passkey->authenticator,
            createdAtDiff: $passkey->created_at?->diffForHumans() ?? '',
            lastUsedAtDiff: $passkey->last_used_at?->diffForHumans(),
        );
    }
}

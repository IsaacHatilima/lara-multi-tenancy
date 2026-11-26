<?php

declare(strict_types=1);

namespace App\Data\Domains;

use App\Models\Domain;
use Spatie\LaravelData\Resource;

final class DomainResource extends Resource
{
    public function __construct(
        public readonly int $id,
        public readonly string $domain,
        public readonly string $slot,
        public readonly string $slotLabel,
    ) {
    }

    public static function fromModel(Domain $domain): self
    {
        return new self(
            id: $domain->id,
            domain: $domain->domain,
            slot: $domain->slot->value,
            slotLabel: $domain->slot->label(),
        );
    }
}

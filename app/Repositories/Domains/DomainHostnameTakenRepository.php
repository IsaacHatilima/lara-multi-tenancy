<?php

declare(strict_types=1);

namespace App\Repositories\Domains;

use App\Models\Domain;

final readonly class DomainHostnameTakenRepository
{
    public function handle(string $hostname, ?Domain $except = null): bool
    {
        return Domain::query()
            ->where('domain', $hostname)
            ->when($except !== null, fn ($query) => $query->whereKeyNot($except?->getKey()))
            ->exists();
    }
}

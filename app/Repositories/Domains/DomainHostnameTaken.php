<?php

declare(strict_types=1);

namespace App\Repositories\Domains;

use App\Models\Domain;

final readonly class DomainHostnameTaken
{
    /**
     * Whether a domain already uses this hostname; the domain with `$domainId` doesn't count.
     */
    public function handle(string $hostname, ?int $domainId = null): bool
    {
        $query = Domain::query();
        $query->where('domain', $hostname);

        if ($domainId !== null) {
            $query->whereKeyNot($domainId);
        }

        return $query->exists();
    }
}

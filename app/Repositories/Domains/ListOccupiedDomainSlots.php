<?php

declare(strict_types=1);

namespace App\Repositories\Domains;

use App\Enums\DomainSlot;
use App\Models\Domain;
use App\Models\Tenant;

final readonly class ListOccupiedDomainSlots
{
    /**
     * The slots the company's domains take; the domain with `$domainId` doesn't count.
     *
     * @return list<DomainSlot>
     */
    public function handle(Tenant $tenant, ?int $domainId = null): array
    {
        $query = Domain::query()->where('tenant_id', $tenant->getTenantKey());

        if ($domainId !== null) {
            $query->whereKeyNot($domainId);
        }

        return array_values($query
            ->get(['id', 'slot'])
            ->map(fn (Domain $domain): DomainSlot => $domain->slot)
            ->all());
    }
}

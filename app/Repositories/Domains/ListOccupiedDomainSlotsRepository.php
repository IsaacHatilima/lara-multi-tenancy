<?php

declare(strict_types=1);

namespace App\Repositories\Domains;

use App\Enums\DomainSlot;
use App\Models\Domain;
use App\Models\Tenant;

final readonly class ListOccupiedDomainSlotsRepository
{
    /**
     * @return list<DomainSlot>
     */
    public function handle(Tenant $tenant, ?Domain $except = null): array
    {
        return array_values(Domain::query()
            ->where('tenant_id', $tenant->getTenantKey())
            ->when($except !== null, fn ($query) => $query->whereKeyNot($except?->getKey()))
            ->get(['id', 'slot'])
            ->map(fn (Domain $domain): DomainSlot => $domain->slot)
            ->all());
    }
}

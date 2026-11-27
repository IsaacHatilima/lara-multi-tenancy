<?php

declare(strict_types=1);

namespace App\Repositories\Domains;

use App\Enums\DomainSlot;
use App\Models\Domain;
use App\Models\Tenant;

final readonly class DomainRepository
{
    public function create(Tenant $tenant, string $hostname, DomainSlot $slot): Domain
    {
        $domain = new Domain;

        $domain->tenant_id = (string) $tenant->getTenantKey();
        $domain->domain = $hostname;
        $domain->slot = $slot;
        $domain->save();

        return $domain;
    }

    public function update(Domain $domain, string $hostname, DomainSlot $slot): Domain
    {
        $domain->domain = $hostname;
        $domain->slot = $slot;
        $domain->save();

        return $domain;
    }

    public function move(Domain $domain, Tenant $destination, DomainSlot $slot): Domain
    {
        $domain->tenant_id = (string) $destination->getTenantKey();
        $domain->slot = $slot;
        $domain->save();

        return $domain;
    }

    public function delete(Domain $domain): void
    {
        $domain->delete();
    }

    /**
     * Whether a domain already uses this hostname; the domain with `$domainId` doesn't count.
     */
    public function hostnameTaken(string $hostname, ?int $domainId = null): bool
    {
        $query = Domain::query()->where('domain', $hostname);

        if ($domainId !== null) {
            $query->whereKeyNot($domainId);
        }

        return $query->exists();
    }

    /**
     * The slots the company's domains take; the domain with `$domainId` doesn't count.
     *
     * @return list<DomainSlot>
     */
    public function occupiedSlots(Tenant $tenant, ?int $domainId = null): array
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

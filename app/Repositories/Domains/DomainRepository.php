<?php

declare(strict_types=1);

namespace App\Repositories\Domains;

use App\Enums\DomainSlot;
use App\Models\Domain;
use App\Models\Tenant;

/**
 * Entry file for all Domain persistence. Each operation lives in its own class in this folder; this class composes
 * them so an action needs one import and one constructor parameter instead of one per operation.
 */
final readonly class DomainRepository
{
    public function __construct(
        private CreateDomain $createDomain,
        private UpdateDomain $updateDomain,
        private MoveDomain $moveDomain,
        private DeleteDomain $deleteDomain,
        private DomainHostnameTaken $domainHostnameTaken,
        private ListOccupiedDomainSlots $listOccupiedDomainSlots,
    ) {
    }

    public function create(Tenant $tenant, string $hostname, DomainSlot $slot): Domain
    {
        return $this->createDomain->handle($tenant, $hostname, $slot);
    }

    public function update(Domain $domain, string $hostname, DomainSlot $slot): Domain
    {
        return $this->updateDomain->handle($domain, $hostname, $slot);
    }

    public function move(Domain $domain, Tenant $destination, DomainSlot $slot): Domain
    {
        return $this->moveDomain->handle($domain, $destination, $slot);
    }

    public function delete(Domain $domain): void
    {
        $this->deleteDomain->handle($domain);
    }

    public function hostnameTaken(string $hostname, ?int $domainId = null): bool
    {
        return $this->domainHostnameTaken->handle($hostname, $domainId);
    }

    /**
     * @return list<DomainSlot>
     */
    public function occupiedSlots(Tenant $tenant, ?int $domainId = null): array
    {
        return $this->listOccupiedDomainSlots->handle($tenant, $domainId);
    }
}

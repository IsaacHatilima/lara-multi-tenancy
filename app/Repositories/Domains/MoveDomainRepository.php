<?php

declare(strict_types=1);

namespace App\Repositories\Domains;

use App\Enums\DomainSlot;
use App\Models\Domain;
use App\Models\Tenant;

final readonly class MoveDomainRepository
{
    public function handle(Domain $domain, Tenant $destination, DomainSlot $slot): Domain
    {
        $domain->tenant_id = (string) $destination->getTenantKey();
        $domain->slot = $slot;
        $domain->save();

        return $domain;
    }
}

<?php

declare(strict_types=1);

namespace App\Repositories\Domains;

use App\Enums\DomainSlot;
use App\Models\Domain;
use App\Models\Tenant;

final readonly class CreateDomain
{
    public function handle(Tenant $tenant, string $hostname, DomainSlot $slot): Domain
    {
        $domain = new Domain();

        $domain->tenant_id = (string) $tenant->getTenantKey();
        $domain->domain = $hostname;
        $domain->slot = $slot;
        $domain->save();

        return $domain;
    }
}

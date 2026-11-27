<?php

declare(strict_types=1);

namespace App\Repositories\Domains;

use App\Enums\DomainSlot;
use App\Models\Domain;

final readonly class UpdateDomain
{
    public function handle(Domain $domain, string $hostname, DomainSlot $slot): Domain
    {
        $domain->domain = $hostname;
        $domain->slot = $slot;
        $domain->save();

        return $domain;
    }
}

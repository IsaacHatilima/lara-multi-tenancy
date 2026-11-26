<?php

declare(strict_types=1);

namespace App\Repositories\Domains;

use App\Enums\DomainSlot;
use App\Models\Domain;

final readonly class UpdateDomainRepository
{
    public function handle(Domain $domain, string $hostname, DomainSlot $slot): Domain
    {
        $domain->fill(['domain' => $hostname, 'slot' => $slot])->save();

        return $domain;
    }
}

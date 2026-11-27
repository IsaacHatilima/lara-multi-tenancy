<?php

declare(strict_types=1);

namespace App\Actions\Domains;

use App\Models\Domain;
use App\Models\Tenant;
use App\Repositories\Domains\DomainRepository;

final readonly class MoveDomainAction
{
    public function __construct(
        private ResolveDomainSlotAction $resolveSlot,
        private DomainRepository $domains,
    ) {
    }

    public function handle(Domain $domain, Tenant $destination): Domain
    {
        $slot = $this->resolveSlot->handle($destination, requested: null);

        return $this->domains->move($domain, $destination, $slot);
    }
}

<?php

declare(strict_types=1);

namespace App\Actions\Domains;

use App\Models\Domain;
use App\Models\Tenant;
use App\Repositories\Domains\MoveDomainRepository;

final readonly class MoveDomainAction
{
    public function __construct(
        private ResolveDomainSlotAction $resolveSlot,
        private MoveDomainRepository $moveDomain,
    ) {
    }

    public function handle(Domain $domain, Tenant $destination): Domain
    {
        $slot = $this->resolveSlot->handle($destination, requested: null);

        return $this->moveDomain->handle($domain, $destination, $slot);
    }
}

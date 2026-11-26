<?php

declare(strict_types=1);

namespace App\Actions\Domains;

use App\Models\Domain;
use App\Models\Tenant;
use App\Repositories\Domains\DeleteDomainRepository;
use App\Repositories\Domains\ListOccupiedDomainSlotsRepository;
use Illuminate\Validation\ValidationException;

final readonly class DeleteDomainAction
{
    public function __construct(
        private ListOccupiedDomainSlotsRepository $occupiedSlots,
        private DeleteDomainRepository $deleteDomain,
    ) {
    }

    /**
     * A company always keeps at least one domain, or nobody could reach it.
     */
    public function handle(Tenant $tenant, Domain $domain): void
    {
        if ($this->occupiedSlots->handle($tenant, except: $domain) === []) {
            throw ValidationException::withMessages([
                'domain' => __('A company must keep at least one domain.'),
            ]);
        }

        $this->deleteDomain->handle($domain);
    }
}

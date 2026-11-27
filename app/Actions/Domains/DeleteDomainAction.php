<?php

declare(strict_types=1);

namespace App\Actions\Domains;

use App\Models\Domain;
use App\Models\Tenant;
use App\Repositories\Domains\DomainRepository;
use Illuminate\Validation\ValidationException;

final readonly class DeleteDomainAction
{
    public function __construct(
        private DomainRepository $domains,
    ) {
    }

    /**
     * A company always keeps at least one domain, or nobody could reach it.
     */
    public function handle(Tenant $tenant, Domain $domain): void
    {
        if ($this->domains->occupiedSlots($tenant, $domain->id) === []) {
            throw ValidationException::withMessages([
                'domain' => __('A company must keep at least one domain.'),
            ]);
        }

        $this->domains->delete($domain);
    }
}

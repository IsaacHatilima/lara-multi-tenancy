<?php

declare(strict_types=1);

namespace App\Actions\Domains;

use App\Data\Domains\DomainResource;
use App\Data\Domains\SaveDomainData;
use App\Models\Domain;
use App\Models\Tenant;
use App\Repositories\Domains\DomainRepository;
use Illuminate\Validation\ValidationException;

final readonly class UpdateDomainAction
{
    public function __construct(
        private DomainRepository $domains,
        private ResolveDomainSlotAction $resolveSlot,
    ) {
    }

    public function handle(Tenant $tenant, Domain $domain, SaveDomainData $data): DomainResource
    {
        if ($this->domains->hostnameTaken($data->hostname, $domain->id)) {
            throw ValidationException::withMessages([
                'domain' => __('This domain is already assigned to a company.'),
            ]);
        }

        $slot = $data->slot === null ? $domain->slot : $this->resolveSlot->handle($tenant, $data->slot, $domain);

        return DomainResource::fromModel($this->domains->update($domain, $data->hostname, $slot));
    }
}

<?php

declare(strict_types=1);

namespace App\Actions\Domains;

use App\Data\Domains\DomainResource;
use App\Data\Domains\SaveDomainData;
use App\Models\Tenant;
use App\Repositories\Domains\CreateDomainRepository;
use App\Repositories\Domains\DomainHostnameTakenRepository;
use Illuminate\Validation\ValidationException;

final readonly class CreateDomainAction
{
    public function __construct(
        private DomainHostnameTakenRepository $hostnameTaken,
        private ResolveDomainSlotAction $resolveSlot,
        private CreateDomainRepository $createDomain,
    ) {
    }

    public function handle(Tenant $tenant, SaveDomainData $data): DomainResource
    {
        if ($this->hostnameTaken->handle($data->hostname)) {
            throw ValidationException::withMessages([
                'domain' => __('This domain is already assigned to a company.'),
            ]);
        }

        $slot = $this->resolveSlot->handle($tenant, $data->slot);

        return DomainResource::fromModel($this->createDomain->handle($tenant, $data->hostname, $slot));
    }
}

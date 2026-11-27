<?php

declare(strict_types=1);

namespace App\Actions\Domains;

use App\Data\Domains\DomainResource;
use App\Data\Domains\SaveDomainData;
use App\Models\Tenant;
use App\Repositories\Domains\DomainRepository;
use Illuminate\Validation\ValidationException;

final readonly class CreateDomainAction
{
    public function __construct(
        private DomainRepository $domains,
        private ResolveDomainSlotAction $resolveSlot,
    ) {
    }

    public function handle(Tenant $tenant, SaveDomainData $data): DomainResource
    {
        if ($this->domains->hostnameTaken($data->hostname)) {
            throw ValidationException::withMessages([
                'domain' => __('This domain is already assigned to a company.'),
            ]);
        }

        $slot = $this->resolveSlot->handle($tenant, $data->slot);

        return DomainResource::fromModel($this->domains->create($tenant, $data->hostname, $slot));
    }
}

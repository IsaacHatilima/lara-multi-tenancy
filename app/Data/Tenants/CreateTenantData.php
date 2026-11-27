<?php

declare(strict_types=1);

namespace App\Data\Tenants;

use App\Data\Domains\DomainHostname;
use App\Data\Domains\SaveDomainData;
use App\Enums\DomainSlot;
use App\Http\Requests\Tenants\CreateTenantRequest;
use Spatie\LaravelData\Data;

/**
 * A new company and the primary domain it is reached on.
 */
final class CreateTenantData extends Data
{
    public function __construct(
        public readonly SaveTenantData $company,
        public readonly SaveDomainData $primaryDomain,
    ) {
    }

    public static function fromRequest(CreateTenantRequest $request): self
    {
        return new self(
            company: SaveTenantData::fromRequest($request),
            primaryDomain: new SaveDomainData(
                hostname: DomainHostname::normalize($request->string('domain')->value()),
                slot: DomainSlot::Primary,
            ),
        );
    }
}

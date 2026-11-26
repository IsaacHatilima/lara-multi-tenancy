<?php

declare(strict_types=1);

namespace App\Data\Tenants;

use App\Data\Domains\DomainResource;
use App\Models\Domain;
use App\Models\Tenant;
use Spatie\LaravelData\Resource;

/**
 * A company with its domains; the domains relation must already be loaded.
 */
final class TenantResource extends Resource
{
    /**
     * @param  list<DomainResource>  $domains
     */
    public function __construct(
        public readonly string $id,
        public readonly string $name,
        public readonly ?string $legalName,
        public readonly ?string $email,
        public readonly ?string $phone,
        public readonly ?string $website,
        public readonly ?string $registrationNumber,
        public readonly ?string $taxNumber,
        public readonly ?string $addressLine1,
        public readonly ?string $addressLine2,
        public readonly ?string $city,
        public readonly ?string $state,
        public readonly ?string $postalCode,
        public readonly ?string $countryCode,
        public readonly ?string $timezone,
        public readonly ?string $locale,
        public readonly array $domains,
        public readonly string $createdAt,
    ) {
    }

    public static function fromModel(Tenant $tenant): self
    {
        return new self(
            id: (string) $tenant->getTenantKey(),
            name: $tenant->name,
            legalName: $tenant->legal_name,
            email: $tenant->email,
            phone: $tenant->phone,
            website: $tenant->website,
            registrationNumber: $tenant->registration_number,
            taxNumber: $tenant->tax_number,
            addressLine1: $tenant->address_line_1,
            addressLine2: $tenant->address_line_2,
            city: $tenant->city,
            state: $tenant->state,
            postalCode: $tenant->postal_code,
            countryCode: $tenant->country_code,
            timezone: $tenant->timezone,
            locale: $tenant->locale,
            domains: array_values($tenant->domains->map(
                fn (Domain $domain): DomainResource => DomainResource::fromModel($domain),
            )->all()),
            createdAt: $tenant->created_at->toIso8601String(),
        );
    }
}

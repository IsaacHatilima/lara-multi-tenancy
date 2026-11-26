<?php

declare(strict_types=1);

namespace App\Data\Tenants;

use App\Models\Tenant;
use Spatie\LaravelData\Resource;

/**
 * The company's legal identity, from its own details, and the agreements it has accepted.
 */
final class TenantComplianceResource extends Resource
{
    /**
     * @param  list<string>  $address
     */
    public function __construct(
        public readonly ?string $legalName,
        public readonly ?string $registrationNumber,
        public readonly ?string $taxNumber,
        public readonly ?string $countryCode,
        public readonly array $address,
        public readonly string $termsVersion,
        public readonly ?string $termsAcceptedAt,
        public readonly ?string $dataProcessingAgreementSignedAt,
        public readonly string $dataRegion,
    ) {
    }

    public static function fromRecords(Tenant $tenant, ComplianceRecord $compliance): self
    {
        $cityLine = trim(implode(' ', array_filter([$tenant->postal_code, $tenant->city])));

        return new self(
            legalName: $tenant->legal_name,
            registrationNumber: $tenant->registration_number,
            taxNumber: $tenant->tax_number,
            countryCode: $tenant->country_code,
            address: array_values(array_filter(
                [$tenant->address_line_1, $tenant->address_line_2, $cityLine, $tenant->state],
                fn (?string $line): bool => $line !== null && $line !== '',
            )),
            termsVersion: $compliance->termsVersion,
            termsAcceptedAt: $compliance->termsAcceptedAt?->toIso8601String(),
            dataProcessingAgreementSignedAt: $compliance->dataProcessingAgreementSignedAt?->toIso8601String(),
            dataRegion: $compliance->dataRegion,
        );
    }
}

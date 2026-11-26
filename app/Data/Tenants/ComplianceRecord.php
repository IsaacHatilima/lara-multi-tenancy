<?php

declare(strict_types=1);

namespace App\Data\Tenants;

use Carbon\CarbonImmutable;

/**
 * The legal agreements a company has accepted and where its data lives; becomes a model once compliance has a table.
 */
final readonly class ComplianceRecord
{
    public function __construct(
        public string $termsVersion,
        public ?CarbonImmutable $termsAcceptedAt,
        public ?CarbonImmutable $dataProcessingAgreementSignedAt,
        public string $dataRegion,
    ) {
    }
}

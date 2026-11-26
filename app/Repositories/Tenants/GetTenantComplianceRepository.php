<?php

declare(strict_types=1);

namespace App\Repositories\Tenants;

use App\Data\Tenants\ComplianceRecord;
use App\Models\Tenant;

final readonly class GetTenantComplianceRepository
{
    private const string TERMS_VERSION = '2026-01';

    private const string DATA_REGION = 'EU (Frankfurt)';

    /**
     * Sample data until accepted agreements have a table: every company accepted the terms when it was created,
     * and roughly half have signed the data processing agreement.
     */
    public function handle(Tenant $tenant): ComplianceRecord
    {
        $createdAt = $tenant->created_at->toImmutable();

        return new ComplianceRecord(
            termsVersion: self::TERMS_VERSION,
            termsAcceptedAt: $createdAt,
            dataProcessingAgreementSignedAt: crc32((string) $tenant->getTenantKey()) % 2 === 0 ? $createdAt : null,
            dataRegion: self::DATA_REGION,
        );
    }
}

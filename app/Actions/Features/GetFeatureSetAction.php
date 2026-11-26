<?php

declare(strict_types=1);

namespace App\Actions\Features;

use App\Data\Features\FeatureSet;
use App\Models\Tenant;
use App\Repositories\Features\TenantFeatureSetCache;
use LogicException;

/**
 * The features available on a host. The deployment mode is decided first, so ungated hosts read neither plans nor the cache;
 * a gated host answers only from the identified tenant's own set.
 */
final readonly class GetFeatureSetAction
{
    public function __construct(
        private IsFeatureGatedAction $isFeatureGated,
        private TenantFeatureSetCache $tenantFeatureSets,
        private GetTenantFeaturesAction $getTenantFeatures,
    ) {
    }

    public function handle(string $host, ?Tenant $tenant): FeatureSet
    {
        if (! $this->isFeatureGated->handle($host)) {
            return FeatureSet::ungated();
        }

        if ($tenant === null) {
            throw new LogicException("Features are gated on [{$host}], but no tenant was identified.");
        }

        return $this->tenantFeatureSets->remember(
            (string) $tenant->getTenantKey(),
            fn (): FeatureSet => $this->getTenantFeatures->handle($tenant),
        );
    }
}

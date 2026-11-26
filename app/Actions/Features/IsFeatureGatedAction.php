<?php

declare(strict_types=1);

namespace App\Actions\Features;

use App\Actions\Tenants\IsTenantManagementAvailableAction;
use Illuminate\Container\Attributes\Config;

/**
 * Features are gated only on a tenant's host of a managed deployment; the central host and self-hosted installs are never limited.
 */
final readonly class IsFeatureGatedAction
{
    public function __construct(
        #[Config('mitarbo.managed_deployment')]
        private bool $managedDeployment,
        private IsTenantManagementAvailableAction $isTenantManagementAvailable,
    ) {
    }

    public function handle(string $host): bool
    {
        return $this->managedDeployment && ! $this->isTenantManagementAvailable->handle($host);
    }
}

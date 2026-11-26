<?php

declare(strict_types=1);

namespace App\Actions\Tenants;

use Illuminate\Container\Attributes\Config;

/**
 * Tenants are managed only from the central host of a managed deployment; tenant hosts and self-hosted installs never see it.
 */
final readonly class IsTenantManagementAvailableAction
{
    /**
     * @param  list<string>  $centralDomains
     */
    public function __construct(
        #[Config('mitarbo.managed_deployment')]
        private bool $managedDeployment,
        #[Config('tenancy.identification.central_domains')]
        private array $centralDomains,
    ) {
    }

    public function handle(string $host): bool
    {
        return $this->managedDeployment && in_array(strtolower($host), $this->centralDomains, true);
    }
}

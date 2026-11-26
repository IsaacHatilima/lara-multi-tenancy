<?php

namespace Database\Seeders;

use App\Enums\CentralPermission;
use Illuminate\Container\Attributes\Config;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

/**
 * Seeds the central staff permissions of a managed deployment in every environment. Granting them is left to
 * CentralUserSeeder for the demo user, and to an operator on a real deployment.
 */
class PermissionSeeder extends Seeder
{
    public function __construct(
        #[Config('mitarbo.managed_deployment')]
        private readonly bool $managedDeployment,
    ) {
    }

    public function run(): void
    {
        if (! $this->managedDeployment) {
            return;
        }

        foreach (CentralPermission::cases() as $permission) {
            Permission::findOrCreate($permission, 'web');
        }
    }
}

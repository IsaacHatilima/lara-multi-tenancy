<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the central database. The plans and permissions are seeded in every environment; the demo data only in local,
     * testing and factory. Model events stay on: creating a tenant is what creates, migrates and seeds its database.
     */
    public function run(): void
    {
        $this->call([
            PlanSeeder::class,
            PermissionSeeder::class,
        ]);

        // 'factory' is a Factory job worktree served by factory/serve.sh.
        if (! app()->environment('local', 'testing', 'factory')) {
            $this->command->warn('Demo users and companies are only seeded in the local and testing environments.');

            return;
        }

        $this->call([
            CentralUserSeeder::class,
            TenantSeeder::class,
        ]);
    }
}

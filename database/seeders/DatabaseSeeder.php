<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the central database. Model events stay on: creating a tenant is what creates, migrates and seeds its database.
     */
    public function run(): void
    {
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

<?php

namespace Database\Seeders;

use App\Models\Plan;
use Illuminate\Container\Attributes\Config;
use Illuminate\Database\Seeder;

/**
 * Seeds the plans of a managed deployment in every environment. A self-hosted install has no plans. A code that
 * already has a plan is left as it is.
 */
class PlanSeeder extends Seeder
{
    /**
     * @var list<array{code: string, name: string, description: string}>
     */
    private const array PLANS = [
        ['code' => 'free', 'name' => 'Free', 'description' => 'For trying the platform.'],
        ['code' => 'basic', 'name' => 'Basic', 'description' => 'For small teams.'],
        ['code' => 'pro', 'name' => 'Pro', 'description' => 'For growing companies.'],
    ];

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

        foreach (self::PLANS as $plan) {
            Plan::query()->firstOrCreate(['code' => $plan['code']], $plan);
        }
    }
}

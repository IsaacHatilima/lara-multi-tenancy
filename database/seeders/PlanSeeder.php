<?php

namespace Database\Seeders;

use App\Enums\BillingInterval;
use App\Enums\Currency;
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
     * Monthly euro prices in cents.
     *
     * @var list<array{code: string, name: string, description: string, price_minor: int}>
     */
    private const array PLANS = [
        ['code' => 'free', 'name' => 'Free', 'description' => 'For trying the platform.', 'price_minor' => 0],
        ['code' => 'basic', 'name' => 'Basic', 'description' => 'For small teams.', 'price_minor' => 1900],
        ['code' => 'pro', 'name' => 'Pro', 'description' => 'For growing companies.', 'price_minor' => 4900],
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
            Plan::query()->firstOrCreate(['code' => $plan['code']], [
                ...$plan,
                'currency' => Currency::Eur,
                'interval' => BillingInterval::Month,
                'trial_days' => 0,
            ]);
        }
    }
}

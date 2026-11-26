<?php

namespace Database\Seeders;

use App\Enums\BillingInterval;
use App\Enums\PlanDeployment;
use App\Enums\PlanStatus;
use App\Models\Plan;
use Illuminate\Container\Attributes\Config;
use Illuminate\Database\Seeder;

/**
 * Seeds the public plans of a managed deployment in every environment, because a tenant without a subscription falls
 * back to the default Free plan. A self-hosted install has no plans. A code that already has a plan is left as it is.
 */
class PlanSeeder extends Seeder
{
    private const string CURRENCY = 'EUR';

    /**
     * In display order. The prices are placeholders.
     *
     * @var list<array{code: string, name: string, price_minor: int, is_default: bool}>
     */
    private const array PLANS = [
        ['code' => 'free', 'name' => 'Free', 'price_minor' => 0, 'is_default' => true],
        ['code' => 'basic', 'name' => 'Basic', 'price_minor' => 1900, 'is_default' => false],
        ['code' => 'pro', 'name' => 'Pro', 'price_minor' => 4900, 'is_default' => false],
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

        foreach (self::PLANS as $sortOrder => $plan) {
            Plan::query()->firstOrCreate(['code' => $plan['code']], [
                ...$plan,
                'status' => PlanStatus::Active,
                'deployment' => PlanDeployment::Hosted,
                'is_public' => true,
                'currency' => self::CURRENCY,
                'interval' => BillingInterval::Month,
                'sort_order' => $sortOrder,
            ]);
        }
    }
}

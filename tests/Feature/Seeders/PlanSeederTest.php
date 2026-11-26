<?php

use App\Enums\BillingInterval;
use App\Enums\PlanDeployment;
use App\Enums\PlanStatus;
use App\Models\Plan;
use Database\Seeders\PlanSeeder;
use Illuminate\Support\Facades\DB;

beforeEach(function () {
    config(['mitarbo.managed_deployment' => true]);
});

test('it creates the public hosted monthly plan', function (string $code, int $priceMinor, bool $isDefault) {
    $this->seed(PlanSeeder::class);

    expect(Plan::query()->where('code', $code)->sole())
        ->status->toBe(PlanStatus::Active)
        ->is_public->toBeTrue()
        ->deployment->toBe(PlanDeployment::Hosted)
        ->interval->toBe(BillingInterval::Month)
        ->trial_days->toBe(0)
        ->currency->toBe('EUR')
        ->price_minor->toBe($priceMinor)
        ->is_default->toBe($isDefault);
})->with([
    'free' => ['free', 0, true],
    'basic' => ['basic', 1900, false],
    'pro' => ['pro', 4900, false],
]);

test('it sorts the plans Free, Basic, Pro', function () {
    $this->seed(PlanSeeder::class);

    expect(Plan::query()->orderBy('sort_order')->pluck('code')->all())->toBe(['free', 'basic', 'pro']);
});

test('it gives the plans no features', function () {
    $this->seed(PlanSeeder::class);

    expect(DB::table('plan_feature')->count())->toBe(0);
});

test('reseeding changes nothing', function () {
    $this->seed(PlanSeeder::class);
    Plan::query()->where('code', 'basic')->update(['name' => 'Basic (renamed)']);

    $this->seed(PlanSeeder::class);

    expect(Plan::query()->count())->toBe(3)
        ->and(Plan::query()->where('code', 'basic')->value('name'))->toBe('Basic (renamed)');
});

test('a self-hosted install gets no plans', function () {
    config(['mitarbo.managed_deployment' => false]);

    $this->seed(PlanSeeder::class);

    expect(Plan::query()->count())->toBe(0);
});

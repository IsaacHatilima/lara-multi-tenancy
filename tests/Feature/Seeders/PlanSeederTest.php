<?php

use App\Models\Plan;
use Database\Seeders\PlanSeeder;

beforeEach(function () {
    config(['mitarbo.managed_deployment' => true]);
});

test('it creates Free, Basic and Pro', function () {
    $this->seed(PlanSeeder::class);

    expect(Plan::query()->orderBy('name')->get(['code', 'name', 'description'])->toArray())->toBe([
        ['code' => 'basic', 'name' => 'Basic', 'description' => 'For small teams.'],
        ['code' => 'free', 'name' => 'Free', 'description' => 'For trying the platform.'],
        ['code' => 'pro', 'name' => 'Pro', 'description' => 'For growing companies.'],
    ]);
});

test('reseeding keeps plans that already exist as they are', function () {
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

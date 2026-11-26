<?php

use App\Data\Plans\CreatePlanData;
use App\Enums\BillingInterval;
use App\Enums\PlanDeployment;
use App\Enums\PlanStatus;
use App\Models\Plan;
use App\Models\PlanFeature;
use App\Models\User;
use App\Repositories\Billing\CreatePlanRepository;

function planToCreate(array $overrides = []): CreatePlanData
{
    return new CreatePlanData(...[
        'code' => 'team',
        'name' => 'Team',
        'description' => 'For growing teams',
        'priceMinor' => 2900,
        'currency' => 'EUR',
        'interval' => BillingInterval::Year,
        'trialDays' => 14,
        'isPublic' => true,
        'sortOrder' => 3,
        'startFromPlanId' => null,
        ...$overrides,
    ]);
}

test('the plan is stored as typed, active, hosted and not default, by the actor', function () {
    $actor = User::factory()->create();

    $plan = app(CreatePlanRepository::class)->handle(planToCreate(), $actor);

    expect(Plan::query()->sole())
        ->id->toBe($plan->id)
        ->code->toBe('team')
        ->name->toBe('Team')
        ->description->toBe('For growing teams')
        ->price_minor->toBe(2900)
        ->currency->toBe('EUR')
        ->interval->toBe(BillingInterval::Year)
        ->trial_days->toBe(14)
        ->is_public->toBeTrue()
        ->sort_order->toBe(3)
        ->status->toBe(PlanStatus::Active)
        ->deployment->toBe(PlanDeployment::Hosted)
        ->is_default->toBeFalse()
        ->tenant_id->toBeNull()
        ->created_by->toBe($actor->id)
        ->updated_by->toBe($actor->id);
});

test('each copied feature row is updated by the actor', function () {
    $actor = User::factory()->create();
    $source = Plan::factory()->create();
    PlanFeature::factory()->count(2)->create(['plan_id' => $source->id, 'updated_by' => User::factory()->create()->id]);

    $plan = app(CreatePlanRepository::class)->handle(planToCreate(['startFromPlanId' => $source->id]), $actor);

    expect(PlanFeature::query()->where('plan_id', $plan->id)->pluck('updated_by')->all())->toBe([$actor->id, $actor->id]);
});

test('a feature that fails to copy saves neither the plan nor any feature', function () {
    $source = Plan::factory()->create();
    PlanFeature::factory()->count(2)->create(['plan_id' => $source->id]);
    $copied = 0;
    PlanFeature::creating(function () use (&$copied): void {
        if (++$copied === 2) {
            throw new RuntimeException('Copy failed.');
        }
    });

    expect(fn () => app(CreatePlanRepository::class)->handle(planToCreate(['startFromPlanId' => $source->id]), User::factory()->create()))
        ->toThrow(RuntimeException::class, 'Copy failed.');
    expect(Plan::query()->pluck('id')->all())->toBe([$source->id])
        ->and(PlanFeature::query()->count())->toBe(2);
});

<?php

use App\Data\Plans\UpdatePlanData;
use App\Models\Feature;
use App\Models\Plan;
use App\Models\PlanFeature;
use App\Models\User;
use App\Repositories\Billing\UpdatePlanRepository;
use Illuminate\Support\Facades\Event;
use Spatie\Activitylog\Models\Activity;
use Stancl\Tenancy\Events\TenantCreated;

beforeEach(function () {
    Event::fake([TenantCreated::class]);

    $this->editor = User::factory()->create();
    $this->plan = Plan::factory()->create([
        'code' => 'basic',
        'name' => 'Basic',
        'description' => 'For small teams',
        'sort_order' => 1,
        'updated_by' => $this->editor->id,
    ]);
    $this->reports = Feature::factory()->create(['key' => 'test-reports']);
    $this->storage = Feature::factory()->create(['key' => 'test-storage']);
    $this->video = Feature::factory()->create(['key' => 'test-video']);
    PlanFeature::factory()->limit(5)->create(['plan_id' => $this->plan->id, 'feature_id' => $this->reports->id, 'updated_by' => $this->editor->id]);
    PlanFeature::factory()->limit(100)->create(['plan_id' => $this->plan->id, 'feature_id' => $this->storage->id, 'updated_by' => $this->editor->id]);
});

/**
 * The plan's fields as they are, with the given changes.
 *
 * @param  array<string, int|null>  $featureLimits
 */
function planEdit(array $overrides = [], array $featureLimits = ['test-reports' => 5, 'test-storage' => 100]): UpdatePlanData
{
    return new UpdatePlanData(...[
        'name' => 'Basic',
        'description' => 'For small teams',
        'sortOrder' => 1,
        'featureLimits' => $featureLimits,
        ...$overrides,
    ]);
}

/**
 * @return array<string, array{limit: int|null, updated_by: int|null}>
 */
function planFeatureRows(Plan $plan): array
{
    return PlanFeature::query()
        ->join('features', 'features.id', '=', 'plan_feature.feature_id')
        ->where('plan_feature.plan_id', $plan->id)
        ->orderBy('features.key')
        ->get(['features.key', 'plan_feature.limit', 'plan_feature.updated_by'])
        ->mapWithKeys(fn (PlanFeature $row): array => [$row->getAttribute('key') => ['limit' => $row->limit, 'updated_by' => $row->updated_by]])
        ->all();
}

test('a change to the plan\'s own fields stores them and sets updated_by to the actor', function () {
    $actor = User::factory()->create();

    app(UpdatePlanRepository::class)->handle($this->plan, planEdit(['name' => 'Starter', 'description' => null, 'sortOrder' => 4]), $actor);

    expect($this->plan->fresh())
        ->name->toBe('Starter')
        ->description->toBeNull()
        ->sort_order->toBe(4)
        ->updated_by->toBe($actor->id);
});

test('added and re-limited features are updated by the actor and an unchanged one keeps its editor', function () {
    $actor = User::factory()->create();

    app(UpdatePlanRepository::class)->handle(
        $this->plan,
        planEdit(featureLimits: ['test-reports' => 5, 'test-storage' => null, 'test-video' => 3]),
        $actor,
    );

    expect(planFeatureRows($this->plan))->toBe([
        'test-reports' => ['limit' => 5, 'updated_by' => $this->editor->id],
        'test-storage' => ['limit' => null, 'updated_by' => $actor->id],
        'test-video' => ['limit' => 3, 'updated_by' => $actor->id],
    ])->and($this->plan->fresh()->updated_by)->toBe($this->editor->id);
});

test('a feature left out is removed', function () {
    app(UpdatePlanRepository::class)->handle($this->plan, planEdit(featureLimits: ['test-storage' => 100]), User::factory()->create());

    expect(array_keys(planFeatureRows($this->plan)))->toBe(['test-storage']);
});

test('only the edited plan changes, not another version of its code or another company\'s plan', function () {
    $oldVersion = Plan::factory()->inactive()->create(['code' => 'basic', 'name' => 'Basic', 'sort_order' => 1]);
    $custom = Plan::factory()->custom()->create(['name' => 'Acme', 'sort_order' => 1]);
    foreach ([$oldVersion, $custom] as $other) {
        PlanFeature::factory()->limit(5)->create(['plan_id' => $other->id, 'feature_id' => $this->reports->id]);
    }
    $before = [$oldVersion->fresh()->toArray(), $custom->fresh()->toArray(), planFeatureRows($oldVersion), planFeatureRows($custom)];

    app(UpdatePlanRepository::class)->handle(
        $this->plan,
        planEdit(['name' => 'Starter'], ['test-reports' => 1, 'test-video' => null]),
        User::factory()->create(),
    );

    expect([$oldVersion->fresh()->toArray(), $custom->fresh()->toArray(), planFeatureRows($oldVersion), planFeatureRows($custom)])
        ->toBe($before);
});

test('a save is logged as one entry with the plan, the actor and the changed fields', function () {
    $actor = User::factory()->create();

    app(UpdatePlanRepository::class)->handle($this->plan, planEdit(['name' => 'Starter', 'sortOrder' => 4]), $actor);

    $activity = Activity::query()->forSubject($this->plan)->sole();
    expect($activity)
        ->event->toBe('updated')
        ->description->toBe('updated')
        ->causer_id->toBe((string) $actor->id)
        ->causer_type->toBe(User::class)
        ->and($activity->attribute_changes?->get('attributes'))->toEqual(['name' => 'Starter', 'sort_order' => 4])
        ->and($activity->attribute_changes?->get('old'))->toEqual(['name' => 'Basic', 'sort_order' => 1]);
});

test('the entry lists the features added, removed and re-limited', function () {
    app(UpdatePlanRepository::class)->handle(
        $this->plan,
        planEdit(featureLimits: ['test-storage' => null, 'test-video' => 3]),
        User::factory()->create(),
    );

    expect(Activity::query()->forSubject($this->plan)->sole())
        ->attribute_changes->get('attributes')->toBe([])
        ->properties->get('features')->toBe([
            'added' => [['key' => 'test-video', 'limit' => 3]],
            'removed' => [['key' => 'test-reports', 'limit' => 5]],
            'relimited' => [['key' => 'test-storage', 'old' => 100, 'new' => null]],
        ]);
});

test('a save that changes nothing logs nothing and keeps the editor', function () {
    app(UpdatePlanRepository::class)->handle($this->plan, planEdit(), User::factory()->create());

    expect(Activity::query()->forSubject($this->plan)->exists())->toBeFalse()
        ->and($this->plan->fresh()->updated_by)->toBe($this->editor->id);
});

test('a feature that fails to save leaves the plan, its features and the log as they were', function () {
    $plan = $this->plan->fresh()->toArray();
    $features = planFeatureRows($this->plan);
    PlanFeature::creating(fn () => throw new RuntimeException('Save failed.'));

    expect(fn () => app(UpdatePlanRepository::class)->handle(
        $this->plan,
        planEdit(['name' => 'Starter'], ['test-storage' => 1, 'test-video' => 3]),
        User::factory()->create(),
    ))->toThrow(RuntimeException::class, 'Save failed.');

    expect($this->plan->fresh()->toArray())->toBe($plan)
        ->and(planFeatureRows($this->plan))->toBe($features)
        ->and(Activity::query()->forSubject($this->plan)->exists())->toBeFalse();
});

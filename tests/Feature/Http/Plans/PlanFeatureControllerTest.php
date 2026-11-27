<?php

use App\Enums\CentralPermission;
use App\Enums\LimitUnit;
use App\Models\Feature;
use App\Models\Plan;
use App\Models\PlanFeature;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use InertiaUI\Notifications\ToastType;
use Spatie\Activitylog\Models\Activity;
use Spatie\Permission\Models\Permission;
use Stancl\Tenancy\Events\TenantCreated;

beforeEach(function () {
    config(['tenancy.bootstrappers' => [], 'mitarbo.managed_deployment' => true]);
    Event::fake([TenantCreated::class]);
    Permission::findOrCreate(CentralPermission::ManagePlans, 'web');
});

afterEach(function () {
    tenancy()->end();
});

function planFeatureManager(): User
{
    return User::factory()->create()->givePermissionTo(CentralPermission::ManagePlans);
}

/**
 * @return array<int, int|null>
 */
function includedLimits(Plan $plan): array
{
    return DB::table('plan_feature')->where('plan_id', $plan->id)->orderBy('feature_id')->pluck('limit', 'feature_id')->all();
}

dataset('plan feature routes', [
    'search' => ['get', 'plans.features.search', fn (Plan $plan, Feature $feature): array => [$plan->id]],
    'add' => ['post', 'plans.features.store', fn (Plan $plan, Feature $feature): array => [$plan->id]],
    'change limit' => ['patch', 'plans.features.update', fn (Plan $plan, Feature $feature): array => [$plan->id, $feature->id]],
    'remove' => ['delete', 'plans.features.destroy', fn (Plan $plan, Feature $feature): array => [$plan->id, $feature->id]],
]);

test('guests are sent to log in and users without plans.manage are forbidden', function (string $method, string $route, Closure $parameters) {
    $plan = Plan::factory()->create();
    $feature = Feature::factory()->create();
    $plan->features()->attach($feature->id, ['limit' => 3]);
    $url = route($route, $parameters($plan, $feature));

    $this->call($method, $url, ['feature_id' => $feature->id])->assertRedirect(route('login'));
    $this->actingAs(User::factory()->create())->call($method, $url, ['feature_id' => $feature->id])->assertForbidden();

    expect(includedLimits($plan))->toBe([$feature->id => 3]);
})->with('plan feature routes');

test('the search offers only features the plan does not include, matching name or key', function () {
    $plan = Plan::factory()->create();
    $included = Feature::factory()->create(['key' => 'chat', 'name' => 'Chat']);
    $plan->features()->attach($included->id);
    $storage = Feature::factory()->create(['key' => 'storage', 'name' => 'File storage', 'limit_unit' => LimitUnit::Megabytes]);
    Feature::factory()->create(['key' => 'crm', 'name' => 'CRM']);

    $this->actingAs(planFeatureManager())
        ->getJson(route('plans.features.search', [$plan, 'search' => 'stor']))
        ->assertOk()
        ->assertExactJson([[
            'id' => $storage->id,
            'key' => 'storage',
            'name' => 'File storage',
            'description' => $storage->description,
            'limitUnit' => 'megabytes',
            'limitUnitLabel' => 'Megabytes',
        ]]);

    $this->actingAs(planFeatureManager())
        ->getJson(route('plans.features.search', [$plan, 'search' => 'chat']))
        ->assertExactJson([]);
});

test('the search returns at most eight features, by name', function () {
    $plan = Plan::factory()->create();
    foreach (range(1, 10) as $number) {
        Feature::factory()->create(['name' => sprintf('Feature %02d', $number)]);
    }

    $this->actingAs(planFeatureManager())
        ->getJson(route('plans.features.search', $plan))
        ->assertJsonCount(8)
        ->assertJsonPath('0.name', 'Feature 01')
        ->assertJsonPath('7.name', 'Feature 08');
});

test('a feature is added with its limit, or unlimited when the limit is blank', function (string $limit, ?int $stored) {
    $plan = Plan::factory()->create();
    $feature = Feature::factory()->create();

    $this->actingAs(planFeatureManager())
        ->post(route('plans.features.store', $plan), ['feature_id' => (string) $feature->id, 'limit' => $limit])
        ->assertRedirect(route('plans.edit', $plan))
        ->assertToast('Feature added.', ToastType::Success);

    expect(includedLimits($plan))->toBe([$feature->id => $stored]);
})->with([
    'limit' => ['25', 25],
    'blank' => ['', null],
]);

test('a feature can be added to a plan only once and must exist', function (Closure $featureId, string $message) {
    $plan = Plan::factory()->create();
    $feature = Feature::factory()->create();
    $plan->features()->attach($feature->id, ['limit' => 3]);

    $this->actingAs(planFeatureManager())
        ->post(route('plans.features.store', $plan), ['feature_id' => $featureId($feature)])
        ->assertSessionHasErrors(['feature_id' => $message]);

    expect(includedLimits($plan))->toBe([$feature->id => 3]);
})->with([
    'already included' => [fn (Feature $feature): int => $feature->id, 'The plan already includes this feature.'],
    'unknown' => [fn (): int => 999999, 'The selected feature id is invalid.'],
]);

test('an invalid limit is refused', function (string $limit) {
    $plan = Plan::factory()->create();
    $feature = Feature::factory()->create();
    $plan->features()->attach($feature->id, ['limit' => 3]);

    $this->actingAs(planFeatureManager())
        ->patch(route('plans.features.update', [$plan, $feature]), ['limit' => $limit])
        ->assertSessionHasErrors('limit');

    expect(includedLimits($plan))->toBe([$feature->id => 3]);
})->with(['not a number' => ['many'], 'negative' => ['-1'], 'too large' => ['2147483648']]);

test('a feature limit is changed, and a blank limit makes it unlimited', function () {
    $plan = Plan::factory()->create();
    [$capped, $unlimited] = Feature::factory()->count(2)->create();
    $plan->features()->attach([$capped->id => ['limit' => 3], $unlimited->id => ['limit' => 3]]);

    $this->actingAs(planFeatureManager())
        ->patch(route('plans.features.update', [$plan, $capped]), ['limit' => '10'])
        ->assertRedirect(route('plans.edit', $plan))
        ->assertToast('Limit saved.', ToastType::Success);
    $this->patch(route('plans.features.update', [$plan, $unlimited]), ['limit' => '']);

    expect(includedLimits($plan))->toBe([$capped->id => 10, $unlimited->id => null]);
});

test('a feature is removed from the plan but kept for other plans', function () {
    [$plan, $other] = Plan::factory()->count(2)->create();
    $feature = Feature::factory()->create();
    $plan->features()->attach($feature->id);
    $other->features()->attach($feature->id);

    $this->actingAs(planFeatureManager())
        ->delete(route('plans.features.destroy', [$plan, $feature]))
        ->assertRedirect(route('plans.edit', $plan))
        ->assertToast('Feature removed.', ToastType::Success);

    expect(includedLimits($plan))->toBe([])
        ->and(includedLimits($other))->toBe([$feature->id => null]);
});

test('changing or removing a feature the plan does not include is not found', function (string $method, string $route) {
    $plan = Plan::factory()->create();
    $feature = Feature::factory()->create();

    $this->actingAs(planFeatureManager())
        ->call($method, route($route, [$plan, $feature]), ['limit' => '1'])
        ->assertNotFound();

    expect(includedLimits($plan))->toBe([]);
})->with([
    'change limit' => ['patch', 'plans.features.update'],
    'remove' => ['delete', 'plans.features.destroy'],
]);

test('adding and changing a feature on a plan is recorded in the activity log', function () {
    $plan = Plan::factory()->create();
    $feature = Feature::factory()->create();

    $this->actingAs(planFeatureManager())
        ->post(route('plans.features.store', $plan), ['feature_id' => (string) $feature->id, 'limit' => '5']);
    $this->patch(route('plans.features.update', [$plan, $feature]), ['limit' => '9']);

    $changes = Activity::query()
        ->where('subject_type', (new PlanFeature())->getMorphClass())
        ->orderBy('id')
        ->get()
        ->map(fn (Activity $activity): array => [$activity->event, $activity->attribute_changes?->get('attributes')])
        ->all();

    expect($changes)->toBe([
        ['created', ['plan_id' => $plan->id, 'feature_id' => $feature->id, 'limit' => 5]],
        ['updated', ['limit' => 9]],
    ]);
});

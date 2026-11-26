<?php

use App\Enums\BillingInterval;
use App\Enums\CentralPermission;
use App\Enums\PlanDeployment;
use App\Enums\PlanStatus;
use App\Models\Domain;
use App\Models\Feature;
use App\Models\Plan;
use App\Models\PlanFeature;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Facades\Event;
use Inertia\Testing\AssertableInertia as Assert;
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

dataset('plan routes', [
    'list' => ['get', 'plans.index', fn (): array => []],
    'create form' => ['get', 'plans.create', fn (): array => []],
    'store' => ['post', 'plans.store', fn (): array => []],
    'edit' => ['get', 'plans.edit', fn (): array => [PlanFeature::factory()->create()->plan_id]],
    'update' => ['put', 'plans.update', fn (): array => [PlanFeature::factory()->create()->plan_id]],
]);

function planManager(): User
{
    return User::factory()->create()->givePermissionTo(CentralPermission::ManagePlans);
}

/**
 * @return array<string, string>
 */
function planForm(array $overrides = []): array
{
    return [
        'code' => 'team',
        'name' => 'Team',
        'description' => 'For growing teams',
        'price' => '29.00',
        'currency' => 'EUR',
        'interval' => 'month',
        'trial_days' => '14',
        'is_public' => '1',
        'sort_order' => '3',
        'start_from' => '',
        ...$overrides,
    ];
}

function onTenantHost(string $route, array $parameters = []): string
{
    Domain::factory()->for(Tenant::factory())->create(['domain' => 'acme.example']);

    return 'https://acme.example'.route($route, $parameters, false);
}

/**
 * The edit page's fields as staff submit them.
 *
 * @return array<string, mixed>
 */
function planEditForm(array $overrides = []): array
{
    return [
        'name' => 'Starter',
        'description' => 'For new teams',
        'sort_order' => '4',
        'features' => [],
        ...$overrides,
    ];
}

/**
 * The features a plan includes, keyed by feature key, each with its limit.
 *
 * @return array<string, int|null>
 */
function includedFeatures(Plan $plan): array
{
    return PlanFeature::query()
        ->join('features', 'features.id', '=', 'plan_feature.feature_id')
        ->where('plan_feature.plan_id', $plan->id)
        ->orderBy('features.key')
        ->pluck('plan_feature.limit', 'features.key')
        ->all();
}

/**
 * Every plan, plan feature row and plan activity entry, to show a refused request wrote nothing.
 *
 * @return array{0: list<array<string, mixed>>, 1: list<array<string, mixed>>, 2: int}
 */
function savedPlans(): array
{
    return [
        Plan::query()->orderBy('id')->get()->toArray(),
        PlanFeature::query()->orderBy('id')->get()->toArray(),
        Activity::query()->where('subject_type', Plan::class)->count(),
    ];
}

test('guests on a tenant domain get not found', function (string $method, string $route, array $parameters) {
    $this->call($method, onTenantHost($route, $parameters), planForm())->assertNotFound();
})->with('plan routes');

test('plan managers on a tenant domain get not found', function (string $method, string $route, array $parameters) {
    $url = onTenantHost($route, $parameters);
    $saved = savedPlans();

    $this->actingAs(planManager())->call($method, $url, planForm())->assertNotFound();

    expect(savedPlans())->toBe($saved);
})->with('plan routes');

test('plan managers on a self-hosted install get not found', function (string $method, string $route, array $parameters) {
    config(['mitarbo.managed_deployment' => false]);
    $saved = savedPlans();

    $this->actingAs(planManager())->call($method, route($route, $parameters), planForm())->assertNotFound();

    expect(savedPlans())->toBe($saved);
})->with('plan routes');

test('guests on the central host are sent to log in', function (string $method, string $route, array $parameters) {
    $this->call($method, route($route, $parameters), planForm())->assertRedirect(route('login'));
})->with('plan routes');

test('unverified users are sent to verify their email before the permission is checked', function (string $method, string $route, array $parameters) {
    $this->actingAs(User::factory()->unverified()->create())
        ->call($method, route($route, $parameters), planForm())
        ->assertRedirect(route('verification.notice'));
})->with('plan routes');

test('central users without plans.manage are forbidden', function (string $method, string $route, array $parameters) {
    $user = User::factory()->create();
    $saved = savedPlans();

    $this->actingAs($user)->call($method, route($route, $parameters), planForm())->assertForbidden();

    expect(savedPlans())->toBe($saved);
})->with('plan routes');

test('a plan is created as typed, active, hosted and not default', function () {
    $response = $this->actingAs(planManager())->post(route('plans.store'), planForm());

    $response->assertRedirect(route('plans.index'))
        ->assertInertiaFlash('toast', ['type' => 'success', 'message' => 'Plan created.']);
    expect(Plan::query()->sole())
        ->code->toBe('team')
        ->name->toBe('Team')
        ->description->toBe('For growing teams')
        ->price_minor->toBe(2900)
        ->currency->toBe('EUR')
        ->interval->toBe(BillingInterval::Month)
        ->trial_days->toBe(14)
        ->is_public->toBeTrue()
        ->sort_order->toBe(3)
        ->status->toBe(PlanStatus::Active)
        ->deployment->toBe(PlanDeployment::Hosted)
        ->is_default->toBeFalse()
        ->tenant_id->toBeNull();
});

test('the price is stored in minor units', function (string $price, int $priceMinor) {
    $this->actingAs(planManager())->post(route('plans.store'), planForm(['price' => $price]))->assertSessionHasNoErrors();

    expect(Plan::query()->sole()->price_minor)->toBe($priceMinor);
})->with([
    'two decimals' => ['19.99', 1999],
    'one decimal' => ['19.9', 1990],
    'whole' => ['19', 1900],
    'zero' => ['0', 0],
]);

test('the currency is stored in upper case', function () {
    $this->actingAs(planManager())->post(route('plans.store'), planForm(['currency' => 'usd']));

    expect(Plan::query()->sole()->currency)->toBe('USD');
});

test('a blank description is stored as null and an unchecked public flag as false', function () {
    $form = planForm(['description' => '   ']);
    unset($form['is_public']);

    $this->actingAs(planManager())->post(route('plans.store'), $form)->assertSessionHasNoErrors();

    expect(Plan::query()->sole())
        ->description->toBeNull()
        ->is_public->toBeFalse();
});

test('a plan starting from another gets its features', function () {
    $source = Plan::factory()->create();
    PlanFeature::factory()->limit(5)->create(['plan_id' => $source->id]);

    $this->actingAs(planManager())->post(route('plans.store'), planForm(['start_from' => $source->id]));

    expect(Plan::query()->where('code', 'team')->sole()->features()->sole()->pivot->limit)->toBe(5);
});

test('a code that already has an active plan is refused', function () {
    Plan::factory()->create(['code' => 'team']);

    $this->actingAs(planManager())
        ->post(route('plans.store'), planForm())
        ->assertSessionHasErrors('code');

    expect(Plan::query()->count())->toBe(1);
});

test('a code that has only an inactive plan is accepted', function () {
    Plan::factory()->inactive()->create(['code' => 'team']);

    $this->actingAs(planManager())
        ->post(route('plans.store'), planForm())
        ->assertSessionHasNoErrors();

    expect(Plan::query()->where('code', 'team')->pluck('status')->all())
        ->toEqualCanonicalizing([PlanStatus::Inactive, PlanStatus::Active]);
});

test('invalid input is refused on its own field', function (string $field, string $value) {
    $this->actingAs(planManager())
        ->post(route('plans.store'), planForm([$field => $value]))
        ->assertSessionHasErrors($field);

    expect(Plan::query()->count())->toBe(0);
})->with([
    'negative price' => ['price', '-1'],
    'price with three decimals' => ['price', '19.999'],
    'negative trial days' => ['trial_days', '-1'],
    'unknown interval' => ['interval', 'week'],
    'unknown currency' => ['currency', 'XYZ'],
    'negative sort order' => ['sort_order', '-1'],
    'missing code' => ['code', ''],
    'missing name' => ['name', ''],
    'start from a plan that does not exist' => ['start_from', '0199d1a0-0000-7000-8000-000000000000'],
    'start from a value that is not a plan id' => ['start_from', 'basic'],
]);

test('plan managers see every hosted plan with its status, price and interval', function () {
    $basic = Plan::factory()->create(['code' => 'basic', 'name' => 'Basic', 'price_minor' => 1900, 'currency' => 'EUR', 'sort_order' => 0]);
    Plan::factory()->custom()->create([
        'code' => 'acme',
        'name' => 'Acme',
        'tenant_id' => Tenant::factory()->create(['name' => 'Acme GmbH'])->id,
        'price_minor' => 9900,
        'currency' => 'USD',
        'interval' => BillingInterval::Year,
        'sort_order' => 1,
    ]);
    Plan::factory()->inactive()->create(['code' => 'pro', 'name' => 'Pro (old)', 'price_minor' => 3900, 'sort_order' => 2]);

    $this->actingAs(planManager())
        ->get(route('plans.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('plans/index')
            ->where('canManagePlans', true)
            ->has('plans', 3)
            ->where('plans.0', [
                'id' => $basic->id,
                'code' => 'basic',
                'name' => 'Basic',
                'status' => 'active',
                'statusLabel' => 'Active',
                'price' => '€19.00',
                'intervalLabel' => 'Monthly',
                'tenantName' => null,
            ])
            ->where('plans.1.code', 'acme')
            ->where('plans.1.tenantName', 'Acme GmbH')
            ->where('plans.1.price', '$99.00')
            ->where('plans.1.intervalLabel', 'Yearly')
            ->where('plans.2.code', 'pro')
            ->where('plans.2.status', 'inactive')
            ->where('plans.2.statusLabel', 'Inactive'));
});

test('without plans the list is empty', function () {
    $this->actingAs(planManager())
        ->get(route('plans.index'))
        ->assertInertia(fn (Assert $page) => $page->component('plans/index')->where('plans', []));
});

test('the shared user carries no roles or permissions after the permission checks', function () {
    $this->actingAs(planManager())
        ->get(route('plans.index'))
        ->assertInertia(fn (Assert $page) => $page
            ->has('auth.user.email')
            ->missing('auth.user.permissions')
            ->missing('auth.user.roles'));
});

test('the create form offers every plan to start from, the intervals and the next display order', function () {
    Plan::factory()->create(['code' => 'basic', 'sort_order' => 0]);
    Plan::factory()->custom()->create(['code' => 'acme', 'sort_order' => 4]);
    Plan::factory()->inactive()->create(['code' => 'pro', 'sort_order' => 1]);

    $this->actingAs(planManager())
        ->get(route('plans.create'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('plans/create')
            ->has('plans', 3)
            ->where('plans.0.code', 'basic')
            ->where('plans.1.code', 'pro')
            ->where('plans.2.code', 'acme')
            ->where('intervals', BillingInterval::options())
            ->where('defaultSortOrder', 5));
});

test('without plans the create form starts the display order at 0', function () {
    $this->actingAs(planManager())
        ->get(route('plans.create'))
        ->assertInertia(fn (Assert $page) => $page->component('plans/create')->where('plans', [])->where('defaultSortOrder', 0));
});

test('an unknown plan is not found', function () {
    $this->actingAs(planManager())->get(route('plans.edit', '0199d1a0-0000-7000-8000-000000000000'))->assertNotFound();
});

test('saving stores the plan\'s name, description and display order', function () {
    $plan = Plan::factory()->create(['name' => 'Basic', 'description' => 'For small teams', 'sort_order' => 1]);

    $this->actingAs(planManager())
        ->put(route('plans.update', $plan), planEditForm())
        ->assertRedirect(route('plans.edit', $plan))
        ->assertInertiaFlash('toast', ['type' => 'success', 'message' => 'Plan updated.']);

    expect($plan->fresh())
        ->name->toBe('Starter')
        ->description->toBe('For new teams')
        ->sort_order->toBe(4);
});

test('a blank description is stored as null', function () {
    $plan = Plan::factory()->create(['description' => 'For small teams']);

    $this->actingAs(planManager())->put(route('plans.update', $plan), planEditForm(['description' => '  ']))->assertSessionHasNoErrors();

    expect($plan->fresh()->description)->toBeNull();
});

test('fields outside the edit page stay as they were', function () {
    $plan = Plan::factory()->create(['code' => 'basic', 'price_minor' => 1900, 'currency' => 'EUR', 'trial_days' => 14, 'sort_order' => 1]);
    $locked = collect($plan->fresh()->toArray())->except(['name', 'description', 'sort_order', 'updated_by', 'updated_at'])->all();
    $manager = planManager();

    $this->actingAs($manager)->put(route('plans.update', $plan), planEditForm([
        'code' => 'pro',
        'price' => '99.00',
        'price_minor' => '9900',
        'currency' => 'USD',
        'interval' => 'year',
        'trial_days' => '30',
        'is_public' => '0',
        'is_default' => '1',
        'status' => 'inactive',
        'deployment' => 'self_hosted',
        'tenant_id' => Tenant::factory()->create()->id,
        'created_by' => User::factory()->create()->id,
        'updated_by' => User::factory()->create()->id,
    ]))->assertSessionHasNoErrors();

    expect(collect($plan->fresh()->toArray())->except(['name', 'description', 'sort_order', 'updated_by', 'updated_at'])->all())->toBe($locked)
        ->and($plan->fresh()->updated_by)->toBe($manager->id);
});

test('saving adds features with and without a limit and removes one left out', function () {
    $plan = Plan::factory()->create();
    PlanFeature::factory()->create(['plan_id' => $plan->id, 'feature_id' => Feature::factory()->create(['key' => 'test-video'])->id]);
    Feature::factory()->create(['key' => 'test-reports']);
    Feature::factory()->create(['key' => 'test-storage']);

    $this->actingAs(planManager())->put(route('plans.update', $plan), planEditForm(['features' => [
        ['key' => 'test-reports', 'limit' => '5'],
        ['key' => 'test-storage', 'limit' => ''],
    ]]))->assertSessionHasNoErrors();

    expect(includedFeatures($plan))->toBe(['test-reports' => 5, 'test-storage' => null]);
});

test('saving re-limits an included feature and clears a limit to unlimited', function () {
    $plan = Plan::factory()->create();
    PlanFeature::factory()->limit(5)->create(['plan_id' => $plan->id, 'feature_id' => Feature::factory()->create(['key' => 'test-reports'])->id]);
    PlanFeature::factory()->limit(100)->create(['plan_id' => $plan->id, 'feature_id' => Feature::factory()->create(['key' => 'test-storage'])->id]);

    $this->actingAs(planManager())->put(route('plans.update', $plan), planEditForm(['features' => [
        ['key' => 'test-reports', 'limit' => '10'],
        ['key' => 'test-storage', 'limit' => ''],
    ]]))->assertSessionHasNoErrors();

    expect(includedFeatures($plan))->toBe(['test-reports' => 10, 'test-storage' => null]);
});

test('invalid edits are refused on their own field and save nothing', function (array $overrides, string $field) {
    $plan = Plan::factory()->create();
    PlanFeature::factory()->limit(5)->create(['plan_id' => $plan->id, 'feature_id' => Feature::factory()->create(['key' => 'test-reports'])->id]);
    $saved = savedPlans();

    $this->actingAs(planManager())
        ->put(route('plans.update', $plan), planEditForm($overrides))
        ->assertSessionHasErrors($field);

    expect(savedPlans())->toBe($saved);
})->with([
    'missing name' => [['name' => ''], 'name'],
    'negative display order' => [['sort_order' => '-1'], 'sort_order'],
    'display order that is not a whole number' => [['sort_order' => '1.5'], 'sort_order'],
    'feature without a row' => [['features' => [['key' => 'test-unknown', 'limit' => '']]], 'features.0.key'],
    'negative limit' => [['features' => [['key' => 'test-reports', 'limit' => '-1']]], 'features.0.limit'],
    'limit that is not a whole number' => [['features' => [['key' => 'test-reports', 'limit' => '2.5']]], 'features.0.limit'],
]);

test('an inactive plan can be edited and stays inactive', function () {
    $plan = Plan::factory()->inactive()->create();

    $this->actingAs(planManager())->put(route('plans.update', $plan), planEditForm())->assertSessionHasNoErrors();

    expect($plan->fresh())
        ->name->toBe('Starter')
        ->status->toBe(PlanStatus::Inactive);
});

test('a custom plan can be edited and keeps its company and visibility', function () {
    $plan = Plan::factory()->custom()->create();

    $this->actingAs(planManager())->put(route('plans.update', $plan), planEditForm())->assertSessionHasNoErrors();

    expect($plan->fresh())
        ->name->toBe('Starter')
        ->tenant_id->toBe($plan->tenant_id)
        ->is_public->toBeFalse();
});

test('a save is logged with the signed-in staff member as causer', function () {
    $plan = Plan::factory()->create();
    $manager = planManager();

    $this->actingAs($manager)->put(route('plans.update', $plan), planEditForm());

    expect(Activity::query()->forSubject($plan)->sole()->causer?->is($manager))->toBeTrue();
});

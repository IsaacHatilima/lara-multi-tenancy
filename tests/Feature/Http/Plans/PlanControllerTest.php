<?php

use App\Enums\BillingInterval;
use App\Enums\CentralPermission;
use App\Enums\Currency;
use App\Enums\LimitUnit;
use App\Models\Domain;
use App\Models\Feature;
use App\Models\Plan;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Inertia\Testing\AssertableInertia as Assert;
use InertiaUI\Notifications\ToastType;
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
    'edit' => ['get', 'plans.edit', fn (): array => [Plan::factory()->create()->id]],
    'update' => ['put', 'plans.update', fn (): array => [Plan::factory()->create()->id]],
    'delete' => ['delete', 'plans.destroy', fn (): array => [Plan::factory()->create()->id]],
]);

function planManager(): User
{
    return User::factory()->create()->givePermissionTo(CentralPermission::ManagePlans);
}

/**
 * @return array<string, mixed>
 */
function planForm(array $overrides = []): array
{
    return [
        'name' => 'Team',
        'code' => 'team',
        'description' => 'For growing teams',
        'price' => '49.00',
        'currency' => 'EUR',
        'interval' => 'month',
        'trial_days' => '0',
        ...$overrides,
    ];
}

function planUrlOnTenantHost(string $route, array $parameters): string
{
    Domain::factory()->for(Tenant::factory())->create(['domain' => 'acme.example']);

    return 'https://acme.example'.route($route, $parameters, false);
}

/**
 * The features a plan includes, keyed by feature id, each with its limit.
 *
 * @return array<int, int|null>
 */
function planLimits(Plan $plan): array
{
    return DB::table('plan_feature')->where('plan_id', $plan->id)->orderBy('feature_id')->pluck('limit', 'feature_id')->all();
}

test('the plans screens are not found on a tenant domain', function (string $method, string $route, array $parameters) {
    $url = planUrlOnTenantHost($route, $parameters);

    $this->call($method, $url, planForm())->assertNotFound();
    $this->actingAs(planManager())->call($method, $url, planForm())->assertNotFound();
})->with('plan routes');

test('the plans screens are not found on a self-hosted install', function (string $method, string $route, array $parameters) {
    config(['mitarbo.managed_deployment' => false]);

    $this->actingAs(planManager())->call($method, route($route, $parameters), planForm())->assertNotFound();
})->with('plan routes');

test('guests are sent to log in', function (string $method, string $route, array $parameters) {
    $this->call($method, route($route, $parameters), planForm())->assertRedirect(route('login'));
})->with('plan routes');

test('users without plans.manage are forbidden and change nothing', function (string $method, string $route, array $parameters) {
    $plans = Plan::query()->orderBy('id')->get()->toArray();

    $this->actingAs(User::factory()->create())->call($method, route($route, $parameters), planForm())->assertForbidden();

    expect(Plan::query()->orderBy('id')->get()->toArray())->toBe($plans);
})->with('plan routes');

test('the list shows every plan by name', function () {
    Plan::factory()->create(['name' => 'Pro']);
    $basic = Plan::factory()->create(['name' => 'Basic', 'code' => 'basic', 'description' => null, 'price_minor' => 1900, 'interval' => BillingInterval::Year, 'trial_days' => 14]);

    $this->actingAs(planManager())->get(route('plans.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('plans/index')
            ->has('plans', 2)
            ->where('plans.0', [
                'id' => $basic->id,
                'name' => 'Basic',
                'code' => 'basic',
                'description' => null,
                'priceMinor' => 1900,
                'price' => '€19.00',
                'currency' => 'EUR',
                'interval' => 'year',
                'intervalLabel' => 'Yearly',
                'trialDays' => 14,
            ])
            ->where('plans.1.name', 'Pro'));
});

test('the create form opens', function () {
    $this->actingAs(planManager())->get(route('plans.create'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('plans/create')
            ->where('currencies.0', ['value' => 'EUR', 'label' => 'EUR'])
            ->where('intervals', [['value' => 'month', 'label' => 'Monthly'], ['value' => 'year', 'label' => 'Yearly']]));
});

test('a plan is created as typed and opened to choose its features', function () {
    $response = $this->actingAs(planManager())->post(route('plans.store'), planForm([
        'name' => '  Team  ',
        'price' => '19.99',
        'currency' => 'USD',
        'interval' => 'year',
        'trial_days' => '14',
    ]));

    $plan = Plan::query()->sole();
    $response->assertRedirect(route('plans.edit', $plan))
        ->assertToast('Plan created.', ToastType::Success);
    expect($plan)
        ->name->toBe('Team')
        ->code->toBe('team')
        ->description->toBe('For growing teams')
        ->price_minor->toBe(1999)
        ->currency->toBe(Currency::Usd)
        ->interval->toBe(BillingInterval::Year)
        ->trial_days->toBe(14)
        ->and(planLimits($plan))->toBe([]);
});

test('a blank description is stored as null', function () {
    $this->actingAs(planManager())->post(route('plans.store'), planForm(['description' => '  ']))->assertSessionHasNoErrors();

    expect(Plan::query()->sole()->description)->toBeNull();
});

test('an invalid new plan is refused', function (array $overrides, string $field) {
    Plan::factory()->create(['code' => 'pro']);

    $this->actingAs(planManager())->post(route('plans.store'), planForm($overrides))->assertSessionHasErrors($field);

    expect(Plan::query()->count())->toBe(1);
})->with([
    'missing name' => [['name' => ''], 'name'],
    'name not a string' => [['name' => ['Team']], 'name'],
    'name too long' => [['name' => str_repeat('a', 256)], 'name'],
    'missing code' => [['code' => ''], 'code'],
    'code not a string' => [['code' => ['team']], 'code'],
    'code too long' => [['code' => str_repeat('a', 256)], 'code'],
    'code taken' => [['code' => 'pro'], 'code'],
    'description not a string' => [['description' => ['text']], 'description'],
    'missing price' => [['price' => ''], 'price'],
    'negative price' => [['price' => '-1'], 'price'],
    'price with three decimals' => [['price' => '9.999'], 'price'],
    'price too high' => [['price' => '1000000.01'], 'price'],
    'unsupported currency' => [['currency' => 'XYZ'], 'currency'],
    'unknown interval' => [['interval' => 'week'], 'interval'],
    'negative trial' => [['trial_days' => '-1'], 'trial_days'],
    'trial too long' => [['trial_days' => '366'], 'trial_days'],
]);

test('the edit form lists every feature with whether the plan includes it', function () {
    $plan = Plan::factory()->create();
    $storage = Feature::factory()->create(['key' => 'storage', 'name' => 'Storage', 'limit_unit' => LimitUnit::Megabytes]);
    $crm = Feature::factory()->create(['key' => 'crm', 'name' => 'CRM']);
    $messaging = Feature::factory()->create(['key' => 'messaging', 'name' => 'Messaging']);
    $plan->features()->attach([$storage->id => ['limit' => 500], $crm->id => ['limit' => null]]);

    $this->actingAs(planManager())->get(route('plans.edit', $plan))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('plans/edit')
            ->where('plan.id', $plan->id)
            ->where('plan.name', $plan->name)
            ->where('plan.priceMinor', $plan->price_minor)
            ->has('currencies')
            ->has('intervals')
            ->where('features', [
                ['id' => $crm->id, 'key' => 'crm', 'name' => 'CRM', 'limitUnitLabel' => null, 'included' => true, 'limit' => null],
                ['id' => $messaging->id, 'key' => 'messaging', 'name' => 'Messaging', 'limitUnitLabel' => null, 'included' => false, 'limit' => null],
                ['id' => $storage->id, 'key' => 'storage', 'name' => 'Storage', 'limitUnitLabel' => 'Megabytes', 'included' => true, 'limit' => 500],
            ]));
});

test('saving a plan updates its fields and maps the ticked features with their limits', function () {
    $plan = Plan::factory()->create(['code' => 'team']);
    [$kept, $added, $removed, $unlimited] = Feature::factory()->count(4)->create();
    $plan->features()->attach([$kept->id => ['limit' => 5], $removed->id => ['limit' => 10]]);

    $response = $this->actingAs(planManager())->put(route('plans.update', $plan), planForm([
        'name' => 'Team Plus',
        'code' => 'team',
        'description' => '',
        'price' => '0',
        'currency' => 'GBP',
        'interval' => 'year',
        'trial_days' => '30',
        'features' => [
            ['id' => (string) $kept->id, 'limit' => '8'],
            ['id' => (string) $added->id, 'limit' => '0'],
            ['id' => (string) $unlimited->id, 'limit' => ''],
        ],
    ]));

    $response->assertRedirect(route('plans.edit', $plan))
        ->assertToast('Plan updated.', ToastType::Success);
    expect($plan->fresh())
        ->name->toBe('Team Plus')
        ->code->toBe('team')
        ->description->toBeNull()
        ->price_minor->toBe(0)
        ->currency->toBe(Currency::Gbp)
        ->interval->toBe(BillingInterval::Year)
        ->trial_days->toBe(30)
        ->and(planLimits($plan))->toBe([$kept->id => 8, $added->id => 0, $unlimited->id => null]);
});

test('saving a plan with no feature ticked removes all its features', function () {
    $plan = Plan::factory()->create();
    $plan->features()->attach(Feature::factory()->create()->id, ['limit' => 3]);

    $this->actingAs(planManager())->put(route('plans.update', $plan), planForm())->assertSessionHasNoErrors();

    expect(planLimits($plan))->toBe([]);
});

test('an invalid plan update is refused and changes nothing', function (Closure $overrides, string $field) {
    $plan = Plan::factory()->create();
    $feature = Feature::factory()->create();
    $plan->features()->attach($feature->id, ['limit' => 3]);
    Plan::factory()->create(['code' => 'pro']);
    $before = [$plan->fresh()->toArray(), planLimits($plan)];

    $this->actingAs(planManager())
        ->put(route('plans.update', $plan), planForm($overrides($feature)))
        ->assertSessionHasErrors($field);

    expect([$plan->fresh()->toArray(), planLimits($plan)])->toBe($before);
})->with([
    'missing name' => [fn (): array => ['name' => ''], 'name'],
    'name not a string' => [fn (): array => ['name' => ['Team']], 'name'],
    'name too long' => [fn (): array => ['name' => str_repeat('a', 256)], 'name'],
    'missing code' => [fn (): array => ['code' => ''], 'code'],
    'code not a string' => [fn (): array => ['code' => ['team']], 'code'],
    'code too long' => [fn (): array => ['code' => str_repeat('a', 256)], 'code'],
    'code of another plan' => [fn (): array => ['code' => 'pro'], 'code'],
    'description not a string' => [fn (): array => ['description' => ['text']], 'description'],
    'features not a list' => [fn (): array => ['features' => 'all'], 'features'],
    'feature without an id' => [fn (): array => ['features' => [['limit' => '1']]], 'features.0.id'],
    'feature id not a number' => [fn (): array => ['features' => [['id' => 'crm']]], 'features.0.id'],
    'unknown feature' => [fn (): array => ['features' => [['id' => '999999']]], 'features.0.id'],
    'feature listed twice' => [fn (Feature $feature): array => ['features' => [
        ['id' => (string) $feature->id, 'limit' => '1'],
        ['id' => (string) $feature->id, 'limit' => '2'],
    ]], 'features.1.id'],
    'limit not a number' => [fn (Feature $feature): array => ['features' => [['id' => (string) $feature->id, 'limit' => 'many']]], 'features.0.limit'],
    'negative limit' => [fn (Feature $feature): array => ['features' => [['id' => (string) $feature->id, 'limit' => '-1']]], 'features.0.limit'],
    'limit too large' => [fn (Feature $feature): array => ['features' => [['id' => (string) $feature->id, 'limit' => '2147483648']]], 'features.0.limit'],
]);

test('deleting a plan removes it and its feature mappings but keeps the features', function () {
    $plan = Plan::factory()->create();
    $kept = Plan::factory()->create();
    $feature = Feature::factory()->create();
    $plan->features()->attach($feature->id, ['limit' => 3]);
    $kept->features()->attach($feature->id, ['limit' => null]);

    $this->actingAs(planManager())->delete(route('plans.destroy', $plan))
        ->assertRedirect(route('plans.index'))
        ->assertToast('Plan deleted.', ToastType::Success);

    expect(Plan::query()->pluck('id')->all())->toBe([$kept->id])
        ->and(DB::table('plan_feature')->pluck('plan_id')->all())->toBe([$kept->id])
        ->and(Feature::query()->count())->toBe(1);
});

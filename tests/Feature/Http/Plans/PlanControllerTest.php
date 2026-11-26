<?php

use App\Enums\BillingInterval;
use App\Enums\CentralPermission;
use App\Enums\PlanDeployment;
use App\Enums\PlanStatus;
use App\Models\Domain;
use App\Models\Plan;
use App\Models\PlanFeature;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Facades\Event;
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
    'list' => ['get', 'plans.index'],
    'create form' => ['get', 'plans.create'],
    'store' => ['post', 'plans.store'],
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

function onTenantHost(string $route): string
{
    Domain::factory()->for(Tenant::factory())->create(['domain' => 'acme.example']);

    return 'https://acme.example'.route($route, absolute: false);
}

test('guests on a tenant domain get not found', function (string $method, string $route) {
    $this->call($method, onTenantHost($route), planForm())->assertNotFound();
})->with('plan routes');

test('plan managers on a tenant domain get not found', function (string $method, string $route) {
    $this->actingAs(planManager())->call($method, onTenantHost($route), planForm())->assertNotFound();

    expect(Plan::query()->count())->toBe(0);
})->with('plan routes');

test('plan managers on a self-hosted install get not found', function (string $method, string $route) {
    config(['mitarbo.managed_deployment' => false]);

    $this->actingAs(planManager())->call($method, route($route), planForm())->assertNotFound();

    expect(Plan::query()->count())->toBe(0);
})->with('plan routes');

test('guests on the central host are sent to log in', function (string $method, string $route) {
    $this->call($method, route($route), planForm())->assertRedirect(route('login'));
})->with('plan routes');

test('unverified users are sent to verify their email before the permission is checked', function (string $method, string $route) {
    $this->actingAs(User::factory()->unverified()->create())
        ->call($method, route($route), planForm())
        ->assertRedirect(route('verification.notice'));
})->with('plan routes');

test('central users without plans.manage are forbidden', function (string $method, string $route) {
    $this->actingAs(User::factory()->create())->call($method, route($route), planForm())->assertForbidden();

    expect(Plan::query()->count())->toBe(0);
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

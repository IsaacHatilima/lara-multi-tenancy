<?php

use App\Enums\CentralPermission;
use App\Enums\LimitUnit;
use App\Models\Domain;
use App\Models\Feature;
use App\Models\Plan;
use App\Models\Tenant;
use App\Models\User;
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

dataset('feature routes', [
    'list' => ['get', 'features.index', fn (): array => []],
    'create form' => ['get', 'features.create', fn (): array => []],
    'store' => ['post', 'features.store', fn (): array => []],
    'edit' => ['get', 'features.edit', fn (): array => [Feature::factory()->create()->id]],
    'update' => ['put', 'features.update', fn (): array => [Feature::factory()->create()->id]],
    'delete' => ['delete', 'features.destroy', fn (): array => [Feature::factory()->create()->id]],
]);

function featureManager(): User
{
    return User::factory()->create()->givePermissionTo(CentralPermission::ManagePlans);
}

/**
 * @return array<string, mixed>
 */
function featureForm(array $overrides = []): array
{
    return [
        'key' => 'messaging',
        'name' => 'Messaging',
        'description' => 'Send messages to colleagues',
        'limit_unit' => 'count',
        ...$overrides,
    ];
}

function featureUrlOnTenantHost(string $route, array $parameters): string
{
    Domain::factory()->for(Tenant::factory())->create(['domain' => 'acme.example']);

    return 'https://acme.example'.route($route, $parameters, false);
}

test('the features screens are not found on a tenant domain', function (string $method, string $route, array $parameters) {
    $url = featureUrlOnTenantHost($route, $parameters);

    $this->call($method, $url, featureForm())->assertNotFound();
    $this->actingAs(featureManager())->call($method, $url, featureForm())->assertNotFound();
})->with('feature routes');

test('the features screens are not found on a self-hosted install', function (string $method, string $route, array $parameters) {
    config(['mitarbo.managed_deployment' => false]);

    $this->actingAs(featureManager())->call($method, route($route, $parameters), featureForm())->assertNotFound();
})->with('feature routes');

test('guests are sent to log in', function (string $method, string $route, array $parameters) {
    $this->call($method, route($route, $parameters), featureForm())->assertRedirect(route('login'));
})->with('feature routes');

test('users without plans.manage are forbidden and change nothing', function (string $method, string $route, array $parameters) {
    $features = Feature::query()->orderBy('id')->get()->toArray();

    $this->actingAs(User::factory()->create())->call($method, route($route, $parameters), featureForm())->assertForbidden();

    expect(Feature::query()->orderBy('id')->get()->toArray())->toBe($features);
})->with('feature routes');

test('the list shows every feature by name', function () {
    Feature::factory()->create(['key' => 'storage', 'name' => 'Storage', 'description' => null, 'limit_unit' => LimitUnit::Megabytes]);
    $messaging = Feature::factory()->create(['key' => 'messaging', 'name' => 'Messaging']);

    $this->actingAs(featureManager())->get(route('features.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('features/index')
            ->has('features.data', 2)
            ->where('filters.search', null)
            ->where('features.data.0', [
                'id' => $messaging->id,
                'key' => 'messaging',
                'name' => 'Messaging',
                'description' => $messaging->description,
                'limitUnit' => null,
                'limitUnitLabel' => null,
            ])
            ->where('features.data.1.limitUnit', 'megabytes')
            ->where('features.data.1.limitUnitLabel', 'Megabytes'));
});

test('the list searches feature names and keys', function (string $search) {
    Feature::factory()->create(['key' => 'file_storage', 'name' => 'Storage']);
    Feature::factory()->create(['key' => 'crm', 'name' => 'CRM']);

    $this->actingAs(featureManager())->get(route('features.index', ['filter' => ['search' => $search]]))
        ->assertInertia(fn (Assert $page) => $page
            ->has('features.data', 1)
            ->where('features.data.0.name', 'Storage')
            ->where('filters.search', $search));
})->with(['name' => ['stor'], 'key' => ['file_']]);

test('the list rejects filters it does not offer', function () {
    $this->actingAs(featureManager())
        ->get(route('features.index', ['filter' => ['limit_unit' => 'count']]))
        ->assertSessionHasErrors('filter');
});

test('the create form offers the limit units', function () {
    $this->actingAs(featureManager())->get(route('features.create'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('features/create')
            ->where('limitUnits', [
                ['value' => 'megabytes', 'label' => 'Megabytes'],
                ['value' => 'count', 'label' => 'Count'],
            ]));
});

test('a feature is created as typed', function () {
    $this->actingAs(featureManager())->post(route('features.store'), featureForm(['name' => '  Messaging  ']))
        ->assertRedirect(route('features.index'))
        ->assertToast('Feature created.', ToastType::Success);

    expect(Feature::query()->sole())
        ->key->toBe('messaging')
        ->name->toBe('Messaging')
        ->description->toBe('Send messages to colleagues')
        ->limit_unit->toBe(LimitUnit::Count);
});

test('a blank description and limit unit are stored as null', function () {
    $this->actingAs(featureManager())->post(route('features.store'), featureForm(['description' => '  ', 'limit_unit' => '']))
        ->assertSessionHasNoErrors();

    expect(Feature::query()->sole())
        ->description->toBeNull()
        ->limit_unit->toBeNull();
});

test('an invalid new feature is refused', function (array $overrides, string $field) {
    Feature::factory()->create(['key' => 'crm']);

    $this->actingAs(featureManager())->post(route('features.store'), featureForm($overrides))->assertSessionHasErrors($field);

    expect(Feature::query()->count())->toBe(1);
})->with([
    'missing key' => [['key' => ''], 'key'],
    'key not a string' => [['key' => ['messaging']], 'key'],
    'key too long' => [['key' => 'k'.str_repeat('a', 255)], 'key'],
    'key with upper case' => [['key' => 'Messaging'], 'key'],
    'key starting with a digit' => [['key' => '2fa'], 'key'],
    'key with a hyphen' => [['key' => 'project-management'], 'key'],
    'key taken' => [['key' => 'crm'], 'key'],
    'missing name' => [['name' => ''], 'name'],
    'name not a string' => [['name' => ['Messaging']], 'name'],
    'name too long' => [['name' => str_repeat('a', 256)], 'name'],
    'description not a string' => [['description' => ['text']], 'description'],
    'unknown limit unit' => [['limit_unit' => 'seats'], 'limit_unit'],
]);

test('a key may hold digits and underscores after its first letter', function () {
    $this->actingAs(featureManager())->post(route('features.store'), featureForm(['key' => 'project_management_2']))
        ->assertSessionHasNoErrors();

    expect(Feature::query()->sole()->key)->toBe('project_management_2');
});

test('the edit form shows the feature and the limit units', function () {
    $feature = Feature::factory()->create(['limit_unit' => LimitUnit::Count]);

    $this->actingAs(featureManager())->get(route('features.edit', $feature))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('features/edit')
            ->where('feature.id', $feature->id)
            ->where('feature.key', $feature->key)
            ->where('feature.limitUnit', 'count')
            ->has('limitUnits', 2));
});

test('a feature is updated, keeping its key', function () {
    $feature = Feature::factory()->create(['key' => 'messaging', 'limit_unit' => LimitUnit::Count]);

    $this->actingAs(featureManager())
        ->put(route('features.update', $feature), featureForm(['key' => 'chat', 'name' => 'Chat', 'description' => '', 'limit_unit' => '']))
        ->assertRedirect(route('features.index'))
        ->assertToast('Feature updated.', ToastType::Success);

    expect($feature->fresh())
        ->key->toBe('messaging')
        ->name->toBe('Chat')
        ->description->toBeNull()
        ->limit_unit->toBeNull();
});

test('an invalid feature update is refused', function (array $overrides, string $field) {
    $feature = Feature::factory()->create();
    $before = $feature->fresh()->toArray();

    $this->actingAs(featureManager())->put(route('features.update', $feature), featureForm($overrides))->assertSessionHasErrors($field);

    expect($feature->fresh()->toArray())->toBe($before);
})->with([
    'missing name' => [['name' => ''], 'name'],
    'name not a string' => [['name' => ['Messaging']], 'name'],
    'name too long' => [['name' => str_repeat('a', 256)], 'name'],
    'description not a string' => [['description' => ['text']], 'description'],
    'unknown limit unit' => [['limit_unit' => 'seats'], 'limit_unit'],
]);

test('deleting a feature removes it from every plan', function () {
    $feature = Feature::factory()->create();
    $kept = Feature::factory()->create();
    $plan = Plan::factory()->create();
    $plan->features()->attach([$feature->id => ['limit' => 5], $kept->id => ['limit' => null]]);

    $this->actingAs(featureManager())->delete(route('features.destroy', $feature))
        ->assertRedirect(route('features.index'))
        ->assertToast('Feature deleted.', ToastType::Success);

    expect(Feature::query()->pluck('id')->all())->toBe([$kept->id])
        ->and($plan->features()->pluck('features.id')->all())->toBe([$kept->id]);
});

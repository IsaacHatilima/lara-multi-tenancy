<?php

use App\Http\Requests\Tenants\UpdateTenantRequest;
use App\Models\Tenant;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Route;
use Stancl\Tenancy\Events\TenantCreated;

beforeEach(function () {
    Route::post('/_test/companies', fn (UpdateTenantRequest $request): Tenant => Tenant::create($request->validated()))
        ->middleware('web');
});

test('company details persist in tenant columns while extra metadata stays in data', function () {
    Event::fake([TenantCreated::class]);
    $details = [
        'name' => 'Acme',
        'legal_name' => 'Acme GmbH',
        'email' => 'hello@acme.example',
        'phone' => '+49301234567',
        'website' => 'https://acme.example',
        'registration_number' => 'HRB 12345',
        'tax_number' => 'DE123456789',
        'address_line_1' => 'Example Street 1',
        'address_line_2' => 'Floor 2',
        'city' => 'Berlin',
        'state' => 'Berlin',
        'postal_code' => '10115',
        'country_code' => 'DE',
        'logo_path' => 'branding/logo.png',
        'timezone' => 'Europe/Berlin',
        'locale' => 'de-DE',
    ];

    $tenant = Tenant::factory()->create([...$details, 'custom_setting' => 'enabled']);

    $this->assertDatabaseHas('tenants', ['id' => $tenant->id, ...$details]);
    expect($tenant->fresh()->only(array_keys($details)))->toBe($details);
    expect(json_decode(DB::table('tenants')->where('id', $tenant->id)->value('data'), true))
        ->toBe(['custom_setting' => 'enabled']);
    Event::assertDispatched(TenantCreated::class);
});

test('a company can be created with only its name and complete optional details later', function () {
    Event::fake([TenantCreated::class]);

    $tenant = Tenant::factory()->create(['name' => 'New Company']);

    $this->assertDatabaseHas('tenants', [
        'id' => $tenant->id,
        'name' => 'New Company',
        'legal_name' => null,
        'email' => null,
        'address_line_1' => null,
        'tax_number' => null,
        'timezone' => null,
        'locale' => null,
    ]);

    $tenant->update(['email' => 'hello@new-company.example', 'country_code' => 'DE']);

    $this->assertDatabaseHas('tenants', [
        'id' => $tenant->id,
        'email' => 'hello@new-company.example',
        'country_code' => 'DE',
    ]);
    Event::assertDispatchedTimes(TenantCreated::class, 1);
});

test('companies cannot be created without a nonblank name', function (?string $name) {
    Event::fake([TenantCreated::class]);

    $this->postJson('/_test/companies', ['name' => $name])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('name')
        ->assertJsonPath('errors.name.0', 'The name field is required.');

    $this->assertDatabaseCount('tenants', 0);
    Event::assertNotDispatched(TenantCreated::class);
})->with([null, '', '   ']);

test('company names reject invalid types and values beyond their storage limit', function (mixed $name, string $message) {
    Event::fake([TenantCreated::class]);

    $this->postJson('/_test/companies', ['name' => $name])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('name')
        ->assertJsonPath('errors.name.0', $message);

    $this->assertDatabaseCount('tenants', 0);
    Event::assertNotDispatched(TenantCreated::class);
})->with([
    'invalid type' => [123, 'The name field must be a string.'],
    'too long' => [str_repeat('A', 256), 'The name field must not be greater than 255 characters.'],
]);

test('company requests accept optional details and exclude tenancy configuration from validated data', function () {
    Event::fake([TenantCreated::class]);

    $this->postJson('/_test/companies', [
        'name' => 'Acme',
        'email' => 'hello@acme.example',
        'website' => 'https://acme.example',
        'timezone' => 'Europe/Berlin',
        'locale' => 'de-DE',
        'country_code' => 'DE',
        'legal_name' => null,
        'tenancy_db_name' => 'untrusted_database',
    ])->assertCreated()->assertJsonPath('name', 'Acme');

    $this->assertDatabaseHas('tenants', [
        'name' => 'Acme',
        'email' => 'hello@acme.example',
        'website' => 'https://acme.example',
        'timezone' => 'Europe/Berlin',
        'locale' => 'de-DE',
        'country_code' => 'DE',
        'legal_name' => null,
    ]);
    expect(Tenant::first()->getAttribute('tenancy_db_name'))->toBeNull();
    Event::assertDispatchedTimes(TenantCreated::class, 1);
});

test('company requests reject invalid optional details before saving', function (string $field, mixed $value, string $message) {
    Event::fake([TenantCreated::class]);

    $this->postJson('/_test/companies', ['name' => 'Acme', $field => $value])
        ->assertUnprocessable()
        ->assertJsonValidationErrors($field)
        ->assertJsonPath('errors.'.$field.'.0', $message);

    $this->assertDatabaseCount('tenants', 0);
    Event::assertNotDispatched(TenantCreated::class);
})->with([
    'email' => ['email', 'invalid', 'The email field must be a valid email address.'],
    'website' => ['website', 'ftp://acme.example', 'The website field must be a valid URL.'],
    'timezone' => ['timezone', 'invalid', 'The timezone field must be a valid timezone.'],
    'country code' => ['country_code', 'Germany', 'The country code field must be 2 characters.'],
    'phone type' => ['phone', 123, 'The phone field must be a string.'],
    'phone length' => ['phone', str_repeat('1', 51), 'The phone field must not be greater than 50 characters.'],
]);

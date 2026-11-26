<?php

use App\Actions\Features\GetFeatureSetAction;
use App\Data\Features\FeatureSet;
use App\Models\Feature;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Facades\Route;
use Inertia\Testing\AssertableInertia as Assert;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;
use Stancl\Tenancy\Middleware\ScopeSessions;

beforeEach(function () {
    config(['mitarbo.managed_deployment' => true, 'tenancy.bootstrappers' => []]);

    Route::middleware(['web', InitializeTenancyByDomain::class, 'universal', ScopeSessions::class, 'feature:test-reports'])
        ->post('test-reports', fn (): string => 'reached');

    Feature::factory()->create(['key' => 'test-reports']);
    $this->tenant = Tenant::withoutEvents(fn (): Tenant => Tenant::factory()->create(['id' => 'acme']));
    $this->tenant->domains()->create(['domain' => 'acme.example.test']);
    $this->user = User::factory()->create();
    $this->tenantVersion = fn (): string => app(GetFeatureSetAction::class)->handle('acme.example.test', $this->tenant)->version();
});

afterEach(function () {
    tenancy()->end();
});

test('signed-in tenant users get their set version', function () {
    $response = $this->actingAs($this->user)->get('https://acme.example.test/dashboard');

    $response->assertOk()->assertHeader('X-Features-Version', ($this->tenantVersion)());
});

test('refusals and redirects carry the version', function (string $method, string $path, int $status) {
    $response = $this->actingAs($this->user)->json($method, 'https://acme.example.test/'.$path);

    $response->assertStatus($status)->assertHeader('X-Features-Version', ($this->tenantVersion)());
})->with([
    'feature refusal' => ['POST', 'test-reports', 403],
    'settings redirect' => ['GET', 'settings', 302],
]);

test('central users get the ungated version', function () {
    $response = $this->actingAs($this->user)->get(config('app.url').'/dashboard');

    $response->assertOk()->assertHeader('X-Features-Version', FeatureSet::ungated()->version());
});

test('guests get no version', function () {
    $response = $this->get('https://acme.example.test/login');

    $response->assertOk()->assertHeaderMissing('X-Features-Version');
});

test('the version matches the features prop', function () {
    $response = $this->actingAs($this->user)->get('https://acme.example.test/dashboard');

    $response->assertInertia(fn (Assert $page) => $page->where('features.version', $response->headers->get('X-Features-Version')));
});

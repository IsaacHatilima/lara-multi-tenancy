<?php

use App\Actions\Features\GetFeatureSetAction;
use App\Models\Feature;
use App\Models\Plan;
use App\Models\PlanFeature;
use App\Models\Subscription;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Facades\Route;
use Inertia\Testing\AssertableInertia as Assert;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;
use Stancl\Tenancy\Middleware\ScopeSessions;

beforeEach(function () {
    config(['mitarbo.managed_deployment' => true, 'tenancy.bootstrappers' => []]);

    foreach (['test-reports', 'test-unknown'] as $key) {
        Route::middleware(['web', InitializeTenancyByDomain::class, 'universal', ScopeSessions::class, 'feature:'.$key])
            ->match(['GET', 'POST', 'PUT', 'PATCH', 'DELETE'], $key, fn (): string => 'reached');
    }

    $this->reports = Feature::factory()->create(['key' => 'test-reports']);
    $this->plan = Plan::factory()->create();
    $this->tenant = Tenant::withoutEvents(fn (): Tenant => Tenant::factory()->create(['id' => 'acme']));
    $this->tenant->domains()->create(['domain' => 'acme.example.test']);
    Subscription::factory()->active()->create(['tenant_id' => $this->tenant->id, 'plan_id' => $this->plan->id]);
});

afterEach(function () {
    tenancy()->end();
});

test('writes the plan lacks are refused', function (string $method) {
    $response = $this->json($method, 'https://acme.example.test/test-reports');

    $response->assertForbidden()->assertExactJson([
        'code' => 'feature_unavailable',
        'feature' => 'test-reports',
        'message' => "Your plan doesn't include this feature.",
    ]);
})->with(['POST', 'PUT', 'PATCH']);

test('reads and deletes pass when the plan lacks the feature', function (string $method) {
    $response = $this->call($method, 'https://acme.example.test/test-reports');

    $response->assertOk();
})->with(['GET', 'HEAD', 'DELETE']);

test('writes pass when the plan includes the feature, whatever its limit', function (string $method) {
    PlanFeature::factory()->limit(1)->create(['plan_id' => $this->plan->id, 'feature_id' => $this->reports->id]);

    $response = $this->json($method, 'https://acme.example.test/test-reports');

    $response->assertOk();
})->with(['POST', 'PUT', 'PATCH']);

test('a key with no feature row is refused', function () {
    $response = $this->postJson('https://acme.example.test/test-unknown');

    $response->assertForbidden()->assertJsonPath('feature', 'test-unknown');
});

test('an ungated host is never refused', function (bool $managed, string $host) {
    config(['mitarbo.managed_deployment' => $managed]);

    $response = $this->postJson($host.'/test-reports');

    $response->assertOk();
})->with([
    'managed central host' => fn (): array => [true, config('app.url')],
    'self-hosted install' => [false, 'https://acme.example.test'],
]);

test('an Inertia write goes back with an error and a toast', function () {
    $response = $this->from('https://acme.example.test/dashboard')
        ->withHeaders(['X-Inertia' => 'true'])
        ->post('https://acme.example.test/test-reports');

    $response->assertRedirect('https://acme.example.test/dashboard')
        ->assertSessionHasErrors(['feature_unavailable' => "Your plan doesn't include this feature."])
        ->assertInertiaFlash('toast', ['type' => 'error', 'message' => "Your plan doesn't include this feature."]);
});

test('a refused Inertia patch is answered with a 303', function () {
    $response = $this->from('https://acme.example.test/dashboard')
        ->withHeaders(['X-Inertia' => 'true'])
        ->patch('https://acme.example.test/test-reports');

    $response->assertStatus(303);
});

test('the page after an Inertia refusal carries the current features', function () {
    $this->actingAs(User::factory()->create())
        ->from('https://acme.example.test/dashboard')
        ->withHeaders(['X-Inertia' => 'true'])
        ->post('https://acme.example.test/test-reports');

    $response = $this->flushHeaders()->get('https://acme.example.test/dashboard');

    $response->assertInertia(fn (Assert $page) => $page
        ->where('features.items.test-reports.enabled', false)
        ->where('features.version', app(GetFeatureSetAction::class)->handle('acme.example.test', $this->tenant)->version()));
});

test('other requests get a plain 403', function () {
    $response = $this->post('https://acme.example.test/test-reports');

    $response->assertForbidden();
    expect($response->isRedirect())->toBeFalse();
});

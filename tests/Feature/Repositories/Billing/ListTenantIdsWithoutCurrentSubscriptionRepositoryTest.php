<?php

use App\Models\Subscription;
use App\Models\Tenant;
use App\Repositories\Billing\ListTenantIdsWithoutCurrentSubscriptionRepository;
use Illuminate\Support\Facades\Event;
use Stancl\Tenancy\Events\TenantCreated;

beforeEach(function () {
    Event::fake([TenantCreated::class]);
});

test('tenants without a current subscription are listed', function () {
    $neverSubscribed = Tenant::factory()->create();
    $former = Tenant::factory()->create();
    Subscription::factory()->canceled()->create(['tenant_id' => $former->id]);
    Subscription::factory()->ended()->create(['tenant_id' => $former->id]);
    $subscribed = Tenant::factory()->create();
    Subscription::factory()->ended()->create(['tenant_id' => $subscribed->id]);
    Subscription::factory()->pastDue()->create(['tenant_id' => $subscribed->id]);

    expect(app(ListTenantIdsWithoutCurrentSubscriptionRepository::class)->handle())
        ->toEqualCanonicalizing([$neverSubscribed->id, $former->id]);
});

<?php

use App\Actions\Domains\MoveDomainAction;
use App\Enums\DomainSlot;
use App\Models\Domain;
use App\Models\Tenant;
use Illuminate\Support\Facades\Event;
use Illuminate\Validation\ValidationException;
use Stancl\Tenancy\Events\TenantCreated;

beforeEach(function () {
    config(['tenancy.bootstrappers' => []]);
    Event::fake([TenantCreated::class]);
});

test('a domain cannot move into a company that already has two domains', function () {
    $domain = Domain::factory()->create();
    $originalTenantId = $domain->tenant_id;
    $fullTenant = Tenant::factory()->create();
    Domain::factory()->for($fullTenant)->bothSlots()->create();

    expect(fn () => app(MoveDomainAction::class)->handle($domain, $fullTenant))
        ->toThrow(ValidationException::class, 'A company may have at most two domains.');

    expect($domain->fresh())->tenant_id->toBe($originalTenantId)->slot->toBe(DomainSlot::Primary);
    $this->assertDatabaseCount('domains', 3);
});

test('a moved domain takes the free slot of its new company', function () {
    $domain = Domain::factory()->create();
    $destination = Domain::factory()->create()->tenant;

    $moved = app(MoveDomainAction::class)->handle($domain, $destination);

    expect($moved->fresh())
        ->tenant_id->toBe($destination->getTenantKey())
        ->slot->toBe(DomainSlot::Secondary);
});

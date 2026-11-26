<?php

use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;
use Laravel\Fortify\Features;

test('passkeys page lists the user passkeys in the shape the page expects', function () {
    Features::passkeys([
        'confirmPassword' => true,
    ]);

    $user = User::factory()->create();
    $passkey = $user->passkeys()->create([
        'name' => 'Work laptop',
        'credential_id' => 'credential-id',
        'credential' => [],
    ]);

    $this->actingAs($user)
        ->withSession(['auth.password_confirmed_at' => time()])
        ->get(route('passkeys.edit'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('settings/passkeys')
            ->where('canManagePasskeys', true)
            ->has('passkeys', 1)
            ->where('passkeys.0', [
                'id' => $passkey->id,
                'name' => 'Work laptop',
                'authenticator' => null,
                'createdAtDiff' => '0 seconds ago',
                'lastUsedAtDiff' => null,
            ]),
        );
});

test('passkeys page requires password confirmation', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('passkeys.edit'))
        ->assertRedirect(route('password.confirm'));
});

test('passkeys page renders without passkeys when feature is disabled', function () {
    config(['fortify.features' => []]);

    $user = User::factory()->create();

    $this->actingAs($user)
        ->withSession(['auth.password_confirmed_at' => time()])
        ->get(route('passkeys.edit'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('settings/passkeys')
            ->where('canManagePasskeys', false)
            ->where('passkeys', []),
        );
});

test('the passkey well-known endpoints point at the passkeys page', function () {
    $this->getJson(route('well-known.passkeys'))
        ->assertOk()
        ->assertExactJson([
            'enroll' => route('passkeys.edit'),
            'manage' => route('passkeys.edit'),
        ]);
});

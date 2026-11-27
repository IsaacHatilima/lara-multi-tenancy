<?php

use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

test('profile page is displayed', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->get(route('profile.edit'));

    $response->assertOk();
});

test('the signed-in user is shared with their profile names', function () {
    $user = User::factory()->named('Ada', 'Lovelace')->create();

    $this->actingAs($user)
        ->get(route('profile.edit'))
        ->assertInertia(fn (Assert $page) => $page
            ->where('auth.user.profile.first_name', 'Ada')
            ->where('auth.user.profile.last_name', 'Lovelace')
            ->where('auth.user.profile.full_name', 'Ada Lovelace')
            ->missing('auth.user.roles'));
});

test('the profile names can be updated without touching the email', function () {
    $user = User::factory()->create(['email' => 'keep@example.com']);

    $this->actingAs($user)
        ->patch(route('profile.update'), [
            'first_name' => ' Test ',
            'last_name' => 'User',
            'email' => 'ignored@example.com',
        ])
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('profile.edit'))
        ->assertToast(fn (array $toast): bool => $toast['title'] === 'Profile updated.'
            && ! array_key_exists('description', $toast));

    $user->refresh();

    expect($user->profile)
        ->first_name->toBe('Test')
        ->last_name->toBe('User')
        ->full_name->toBe('Test User')
        ->and($user->email)->toBe('keep@example.com')
        ->and($user->email_verified_at)->not->toBeNull();
});

test('the profile needs both names', function (string $field) {
    $this->actingAs(User::factory()->create())
        ->patch(route('profile.update'), ['first_name' => 'Test', 'last_name' => 'User', $field => ''])
        ->assertSessionHasErrors($field);
})->with(['first_name', 'last_name']);

test('the email can be changed without the profile names and must be verified again', function () {
    $user = User::factory()->named('Ada', 'Lovelace')->create();

    $this->actingAs($user)
        ->patch(route('user-email.update'), ['email' => 'test@example.com'])
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('profile.edit'))
        ->assertToast(fn (array $toast): bool => $toast['title'] === 'Email updated.'
            && $toast['description'] === 'Verify your new email address to keep full access.');

    $user->refresh();

    expect($user->email)->toBe('test@example.com')
        ->and($user->email_verified_at)->toBeNull()
        ->and($user->profile->full_name)->toBe('Ada Lovelace');
});

test('email is stored in lowercase so the user can still log in', function () {
    $user = User::factory()->create();

    $this
        ->actingAs($user)
        ->patch(route('user-email.update'), ['email' => 'Test@Example.com'])
        ->assertSessionHasNoErrors();

    expect($user->refresh()->email)->toBe('test@example.com');

    $this->post(route('logout'));

    $this->post(route('login.store'), [
        'email' => 'Test@Example.com',
        'password' => 'password',
    ]);

    $this->assertAuthenticatedAs($user);
});

test('email must be unique regardless of case', function () {
    User::factory()->create(['email' => 'taken@example.com']);

    $user = User::factory()->create();

    $this
        ->actingAs($user)
        ->from(route('profile.edit'))
        ->patch(route('user-email.update'), ['email' => 'Taken@Example.com'])
        ->assertSessionHasErrors('email')
        ->assertRedirect(route('profile.edit'));
});

test('email verification status is unchanged when the email address is unchanged', function () {
    $user = User::factory()->create();

    $this
        ->actingAs($user)
        ->patch(route('user-email.update'), ['email' => $user->email])
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('profile.edit'))
        ->assertToast(fn (array $toast): bool => $toast['title'] === 'Email updated.'
            && ! array_key_exists('description', $toast));

    expect($user->refresh()->email_verified_at)->not->toBeNull();
});

test('user can delete their account', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->delete(route('profile.destroy'), [
            'password' => 'password',
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('home'));

    $this->assertGuest();
    expect($user->fresh())->toBeNull();
});

test('correct password must be provided to delete account', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->from(route('profile.edit'))
        ->delete(route('profile.destroy'), [
            'password' => 'wrong-password',
        ]);

    $response
        ->assertSessionHasErrors('password')
        ->assertRedirect(route('profile.edit'));

    expect($user->fresh())->not->toBeNull();
});

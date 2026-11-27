<?php

use App\Actions\Users\UpdateUserAction;
use App\Data\Users\UpdateUserData;
use App\Models\User;

test('fields left out of the data are not written', function () {
    $user = User::factory()->create(['email' => 'keep@example.com']);

    app(UpdateUserAction::class)->handle($user, new UpdateUserData());

    expect($user->fresh())
        ->email->toBe('keep@example.com')
        ->email_verified_at->not->toBeNull();
});

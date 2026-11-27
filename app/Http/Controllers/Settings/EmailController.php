<?php

declare(strict_types=1);

namespace App\Http\Controllers\Settings;

use App\Actions\Users\UpdateUserAction;
use App\Data\Users\UpdateUserData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\EmailUpdateRequest;
use App\Models\User;
use Illuminate\Container\Attributes\CurrentUser;
use Illuminate\Http\RedirectResponse;
use InertiaUI\Notifications\Toast;

final class EmailController extends Controller
{
    /**
     * Change the user's email address.
     */
    public function update(
        EmailUpdateRequest $request,
        #[CurrentUser]
        User $user,
        UpdateUserAction $updateUser,
    ): RedirectResponse {
        $user = $updateUser->handle($user, UpdateUserData::fromEmailRequest($request));

        $toast = Toast::success('Email updated.');

        if (! $user->hasVerifiedEmail()) {
            $toast->description('Verify your new email address to keep full access.');
        }

        $toast->flash();

        return to_route('profile.edit');
    }
}

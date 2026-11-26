<?php

declare(strict_types=1);

namespace App\Http\Controllers\Settings;

use App\Actions\Users\UpdateUserPasswordAction;
use App\Data\Users\UpdatePasswordData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\PasswordUpdateRequest;
use App\Models\User;
use Illuminate\Container\Attributes\CurrentUser;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;
use Inertia\Response;
use InertiaUI\Notifications\Toast;

class PasswordController extends Controller
{
    /**
     * Show the user's password settings page.
     */
    public function edit(): Response
    {
        return Inertia::render('settings/password', [
            'passwordRules' => Password::defaults()->toPasswordRulesString(),
        ]);
    }

    /**
     * Update the user's password.
     */
    public function update(
        PasswordUpdateRequest $request,
        #[CurrentUser]
        User $user,
        UpdateUserPasswordAction $updatePassword,
    ): RedirectResponse {
        $updatePassword->handle($user, UpdatePasswordData::fromRequest($request));

        Toast::success('Password updated.')->flash();

        return back();
    }
}

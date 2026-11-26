<?php

declare(strict_types=1);

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\TwoFactorAuthenticationRequest;
use App\Models\User;
use Illuminate\Container\Attributes\CurrentUser;
use Inertia\Inertia;
use Inertia\Response;
use Laravel\Fortify\Features;

class TwoFactorAuthenticationController extends Controller
{
    /**
     * Show the user's two-factor authentication settings page.
     */
    public function edit(
        TwoFactorAuthenticationRequest $request,
        #[CurrentUser]
        User $user,
    ): Response {
        if (! Features::canManageTwoFactorAuthentication()) {
            return Inertia::render('settings/two-factor', ['canManageTwoFactor' => false]);
        }

        $request->ensureStateIsValid();

        return Inertia::render('settings/two-factor', [
            'canManageTwoFactor' => true,
            'twoFactorEnabled' => $user->hasEnabledTwoFactorAuthentication(),
            'requiresConfirmation' => Features::optionEnabled(Features::twoFactorAuthentication(), 'confirm'),
        ]);
    }
}

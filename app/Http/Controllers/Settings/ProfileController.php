<?php

declare(strict_types=1);

namespace App\Http\Controllers\Settings;

use App\Actions\Users\DeleteUserAction;
use App\Actions\Users\UpdateProfileAction;
use App\Data\Users\UpdateProfileData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\ProfileDeleteRequest;
use App\Http\Requests\Settings\ProfileUpdateRequest;
use App\Models\User;
use Illuminate\Container\Attributes\CurrentUser;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;
use InertiaUI\Notifications\Toast;

class ProfileController extends Controller
{
    /**
     * Show the user's profile settings page.
     */
    public function edit(Request $request): Response
    {
        return Inertia::render('settings/profile', [
            'mustVerifyEmail' => $request->user() instanceof MustVerifyEmail,
            'status' => $request->session()->get('status'),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(
        ProfileUpdateRequest $request,
        #[CurrentUser]
        User $user,
        UpdateProfileAction $updateProfile,
    ): RedirectResponse {
        $user = $updateProfile->handle($user, UpdateProfileData::fromRequest($request));

        $toast = Toast::success('Profile updated.');

        if (! $user->hasVerifiedEmail()) {
            $toast->description('Verify your new email address to keep full access.');
        }

        $toast->flash();

        return to_route('profile.edit');
    }

    /**
     * Delete the user's profile.
     */
    public function destroy(
        ProfileDeleteRequest $request,
        #[CurrentUser]
        User $user,
        DeleteUserAction $deleteUser,
    ): RedirectResponse {
        Auth::logout();

        $deleteUser->handle($user);

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}

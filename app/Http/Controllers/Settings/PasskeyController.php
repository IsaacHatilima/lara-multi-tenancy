<?php

declare(strict_types=1);

namespace App\Http\Controllers\Settings;

use App\Actions\Users\ListPasskeysAction;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Container\Attributes\CurrentUser;
use Inertia\Inertia;
use Inertia\Response;
use Laravel\Fortify\Features;

class PasskeyController extends Controller
{
    /**
     * Show the user's passkey settings page.
     */
    public function edit(
        #[CurrentUser]
        User $user,
        ListPasskeysAction $listPasskeys,
    ): Response {
        $canManagePasskeys = Features::canManagePasskeys();

        return Inertia::render('settings/passkeys', [
            'canManagePasskeys' => $canManagePasskeys,
            'passkeys' => $canManagePasskeys ? $listPasskeys->handle($user) : [],
        ]);
    }
}

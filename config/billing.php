<?php

declare(strict_types=1);

return [
    'grace_days' => (int) env('BILLING_GRACE_DAYS', 7),

    /*
     * Write routes a read-only tenant keeps: authentication and the user's own security settings.
     * A new authentication, billing or data-export route adds its name here.
     */
    'read_only_exempt' => [
        'login.store',
        'logout',
        'password.email',
        'password.update',
        'verification.send',
        'password.confirm.store',
        'two-factor.login.store',
        'passkey.login',
        'passkey.confirm',
        'register.store',
        'user-password.update',
        'two-factor.enable',
        'two-factor.confirm',
        'two-factor.regenerate-recovery-codes',
        'passkey.store',
    ],
];

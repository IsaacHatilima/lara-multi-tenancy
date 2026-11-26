<?php

declare(strict_types=1);

namespace App\Data\Users;

use Illuminate\Support\Str;

final class UserEmail
{
    /**
     * Normalize an email address the way Fortify looks it up when the user logs in.
     */
    public static function normalize(string $email): string
    {
        $email = trim($email);

        return config('fortify.lowercase_usernames') ? Str::lower($email) : $email;
    }
}

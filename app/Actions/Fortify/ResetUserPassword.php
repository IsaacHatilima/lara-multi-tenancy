<?php

declare(strict_types=1);

namespace App\Actions\Fortify;

use App\Actions\Users\UpdateUserPasswordAction;
use App\Concerns\PasswordValidationRules;
use App\Data\Users\UpdatePasswordData;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Laravel\Fortify\Contracts\ResetsUserPasswords;

class ResetUserPassword implements ResetsUserPasswords
{
    use PasswordValidationRules;

    public function __construct(
        private readonly UpdateUserPasswordAction $updatePassword,
    ) {
    }

    /**
     * Validate and reset the user's forgotten password.
     *
     * @param  array<string, string>  $input
     */
    public function reset(User $user, array $input): void
    {
        /** @var array{password: string} $validated */
        $validated = Validator::make($input, [
            'password' => $this->passwordRules(),
        ])->validate();

        $this->updatePassword->handle($user, UpdatePasswordData::fromValidated($validated));
    }
}

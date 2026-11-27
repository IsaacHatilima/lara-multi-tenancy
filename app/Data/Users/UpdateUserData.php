<?php

declare(strict_types=1);

namespace App\Data\Users;

use App\Http\Requests\Settings\EmailUpdateRequest;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Optional;

/**
 * A change to the users columns. A field left Optional is not written.
 */
final class UpdateUserData extends Data
{
    public function __construct(
        public readonly string|Optional $email = new Optional(),
    ) {
    }

    public static function fromEmailRequest(EmailUpdateRequest $request): self
    {
        return new self(email: UserEmail::normalize($request->string('email')->value()));
    }
}

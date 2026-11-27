<?php

declare(strict_types=1);

namespace App\Repositories\Passkeys;

use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Laravel\Passkeys\Passkey;

final readonly class PasskeyRepository
{
    /**
     * @return Collection<int, Passkey>
     */
    public function listForUser(User $user): Collection
    {
        return Passkey::query()
            ->whereBelongsTo($user)
            ->select(['id', 'name', 'credential', 'created_at', 'last_used_at'])
            ->latest()
            ->get();
    }
}

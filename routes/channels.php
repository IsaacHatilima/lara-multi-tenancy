<?php

use App\Models\User;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function (User $user, string $id): bool {
    return ! tenancy()->initialized
        && (string) $user->getAuthIdentifier() === $id;
});

tenant_channel('App.Models.User.{id}', function (User $user, string $tenant, string $id): bool {
    return tenancy()->initialized
        && (string) tenant()->getTenantKey() === $tenant
        && (string) $user->getAuthIdentifier() === $id;
});

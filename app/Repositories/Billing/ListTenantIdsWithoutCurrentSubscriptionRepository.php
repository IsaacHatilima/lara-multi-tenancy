<?php

declare(strict_types=1);

namespace App\Repositories\Billing;

use App\Models\Tenant;
use Illuminate\Database\Eloquent\Builder;

final readonly class ListTenantIdsWithoutCurrentSubscriptionRepository
{
    /**
     * The tenants on the default plan: those that never subscribed or whose subscriptions were all canceled or ended.
     *
     * @return list<string>
     */
    public function handle(): array
    {
        return array_values(Tenant::query()
            ->whereDoesntHave('subscriptions', fn (Builder $query): Builder => $query->current())
            ->pluck('id')
            ->map(strval(...))
            ->all());
    }
}

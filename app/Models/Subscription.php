<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\SubscriptionStatus;
use App\Observers\SubscriptionObserver;
use Carbon\CarbonImmutable;
use Database\Factories\SubscriptionFactory;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Stancl\Tenancy\Database\Concerns\CentralConnection;

/**
 * A tenant's subscription to a plan. A tenant has at most one current (trialing, active, past due or read-only)
 * subscription; canceled and ended ones are its history.
 *
 * @property string $id
 * @property string $tenant_id
 * @property string $plan_id
 * @property SubscriptionStatus $status
 * @property CarbonImmutable|null $trial_ends_at
 * @property CarbonImmutable|null $current_period_ends_at
 * @property CarbonImmutable|null $grace_ends_at
 * @property CarbonImmutable|null $canceled_at
 * @property CarbonImmutable|null $ends_at
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 *
 * @property-read Plan $plan
 */
#[ObservedBy(SubscriptionObserver::class)]
class Subscription extends Model
{
    use CentralConnection;

    /** @use HasFactory<SubscriptionFactory> */
    use HasFactory;

    use HasUuids;

    /**
     * @return BelongsTo<Plan, $this>
     */
    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => SubscriptionStatus::class,
            'trial_ends_at' => 'datetime',
            'current_period_ends_at' => 'datetime',
            'grace_ends_at' => 'datetime',
            'canceled_at' => 'datetime',
            'ends_at' => 'datetime',
        ];
    }

    /**
     * The current subscriptions, of which the subscriptions migration's partial unique index allows one per tenant.
     *
     * @param  Builder<self>  $query
     */
    #[Scope]
    protected function current(Builder $query): void
    {
        $query->whereIn('status', [
            SubscriptionStatus::Trialing,
            SubscriptionStatus::Active,
            SubscriptionStatus::PastDue,
            SubscriptionStatus::ReadOnly,
        ]);
    }
}

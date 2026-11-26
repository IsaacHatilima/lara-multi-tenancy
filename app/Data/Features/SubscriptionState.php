<?php

declare(strict_types=1);

namespace App\Data\Features;

use App\Enums\SubscriptionStatus;
use Carbon\CarbonImmutable;

/**
 * The parts of a tenant's current subscription that enforcement reads: its status and when its trial and grace end.
 */
final readonly class SubscriptionState
{
    public function __construct(
        public SubscriptionStatus $status,
        public ?CarbonImmutable $trialEndsAt,
        public ?CarbonImmutable $graceEndsAt,
    ) {
    }
}

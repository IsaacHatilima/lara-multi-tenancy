<?php

declare(strict_types=1);

namespace App\Data\Features;

use App\Enums\SubscriptionStatus;
use Carbon\CarbonImmutable;

/**
 * The parts of a tenant's current subscription that enforcement reads: its status and when its trial and grace end.
 *
 * @phpstan-type SubscriptionStateArray array{status: string, trialEndsAt: string|null, graceEndsAt: string|null}
 */
final readonly class SubscriptionState
{
    public function __construct(
        public SubscriptionStatus $status,
        public ?CarbonImmutable $trialEndsAt,
        public ?CarbonImmutable $graceEndsAt,
    ) {
    }

    /**
     * @param  SubscriptionStateArray  $state
     */
    public static function fromArray(array $state): self
    {
        return new self(
            status: SubscriptionStatus::from($state['status']),
            trialEndsAt: $state['trialEndsAt'] === null ? null : CarbonImmutable::parse($state['trialEndsAt']),
            graceEndsAt: $state['graceEndsAt'] === null ? null : CarbonImmutable::parse($state['graceEndsAt']),
        );
    }

    /**
     * @return SubscriptionStateArray
     */
    public function toArray(): array
    {
        return [
            'status' => $this->status->value,
            'trialEndsAt' => $this->trialEndsAt?->toIso8601String(),
            'graceEndsAt' => $this->graceEndsAt?->toIso8601String(),
        ];
    }
}

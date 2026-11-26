<?php

declare(strict_types=1);

namespace App\Data\Features;

use App\Enums\Feature;

/**
 * What a tenant may use, worked out from its plan. An ungated set, for the central host and self-hosted installs, allows everything.
 */
final readonly class FeatureSet
{
    /**
     * @param  array<string, FeatureItem>  $items  keyed by feature key
     */
    private function __construct(
        public bool $gated,
        public ?string $planName,
        public ?string $planCode,
        public ?SubscriptionState $subscription,
        public bool $readOnly,
        public array $items,
    ) {
    }

    /**
     * @param  array<string, FeatureItem>  $items
     */
    public static function gated(?string $planName, ?string $planCode, ?SubscriptionState $subscription, bool $readOnly, array $items): self
    {
        return new self(true, $planName, $planCode, $subscription, $readOnly, $items);
    }

    public static function ungated(): self
    {
        $features = Feature::cases();

        $items = array_combine(
            array_map(fn (Feature $feature): string => $feature->value, $features),
            array_map(fn (Feature $feature): FeatureItem => new FeatureItem(true, null, $feature->limitUnit(), []), $features),
        );

        return new self(false, null, null, null, false, $items);
    }

    public function isEnabled(string $key): bool
    {
        return ! $this->gated || ($this->items[$key]->enabled ?? false);
    }

    /**
     * The feature's limit, or null when it is unlimited, not included or unknown.
     */
    public function limit(string $key): ?int
    {
        return $this->items[$key]->limit ?? null;
    }

    /**
     * Whether the feature is included and the amount, in the feature's own unit, is within its limit.
     */
    public function allows(string $key, int $amount): bool
    {
        $limit = $this->limit($key);

        return $this->isEnabled($key) && ($limit === null || $amount <= $limit);
    }
}

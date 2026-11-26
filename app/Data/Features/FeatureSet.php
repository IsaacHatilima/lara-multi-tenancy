<?php

declare(strict_types=1);

namespace App\Data\Features;

use App\Enums\Feature;

/**
 * What a tenant may use, worked out from its plan. An ungated set, for the central host and self-hosted installs, allows everything.
 *
 * @phpstan-import-type FeatureItemArray from FeatureItem
 * @phpstan-import-type SubscriptionStateArray from SubscriptionState
 *
 * @phpstan-type FeatureSetArray array{
 *     gated: bool,
 *     planName: string|null,
 *     planCode: string|null,
 *     subscription: SubscriptionStateArray|null,
 *     readOnly: bool,
 *     items: array<string, FeatureItemArray>,
 * }
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

    /**
     * @param  FeatureSetArray  $set
     */
    public static function fromArray(array $set): self
    {
        return new self(
            gated: $set['gated'],
            planName: $set['planName'],
            planCode: $set['planCode'],
            subscription: $set['subscription'] === null ? null : SubscriptionState::fromArray($set['subscription']),
            readOnly: $set['readOnly'],
            items: array_map(FeatureItem::fromArray(...), $set['items']),
        );
    }

    /**
     * A short hash of the whole set, so equal sets share it whatever order their items were read in.
     */
    public function version(): string
    {
        $set = $this->toArray();
        ksort($set['items']);

        return substr(hash('sha256', json_encode($set, JSON_THROW_ON_ERROR)), 0, 8);
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

    /**
     * @return FeatureSetArray
     */
    public function toArray(): array
    {
        return [
            'gated' => $this->gated,
            'planName' => $this->planName,
            'planCode' => $this->planCode,
            'subscription' => $this->subscription?->toArray(),
            'readOnly' => $this->readOnly,
            'items' => array_map(fn (FeatureItem $item): array => $item->toArray(), $this->items),
        ];
    }
}

<?php

declare(strict_types=1);

namespace App\Data\Features;

use Spatie\LaravelData\Resource;

/**
 * The features the frontend reads, with the version every response stamps so a client can tell its copy is stale.
 *
 * @phpstan-import-type FeatureItemArray from FeatureItem
 * @phpstan-import-type SubscriptionStateArray from SubscriptionState
 */
final class FeatureSetResource extends Resource
{
    /**
     * @param  array{name: string, code: string}|null  $plan
     * @param  SubscriptionStateArray|null  $subscription
     * @param  array<string, FeatureItemArray>  $items
     */
    public function __construct(
        public readonly string $version,
        public readonly bool $gated,
        public readonly ?array $plan,
        public readonly ?array $subscription,
        public readonly bool $readOnly,
        public readonly array $items,
    ) {
    }

    public static function fromFeatureSet(FeatureSet $set): self
    {
        $array = $set->toArray();

        return new self(
            version: $set->version(),
            gated: $array['gated'],
            plan: $array['planName'] === null || $array['planCode'] === null
                ? null
                : ['name' => $array['planName'], 'code' => $array['planCode']],
            subscription: $array['subscription'],
            readOnly: $array['readOnly'],
            items: $array['items'],
        );
    }
}

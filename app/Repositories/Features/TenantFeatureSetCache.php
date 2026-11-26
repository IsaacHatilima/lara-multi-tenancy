<?php

declare(strict_types=1);

namespace App\Repositories\Features;

use App\Data\Features\FeatureSet;
use Closure;
use Illuminate\Container\Attributes\Cache;
use Illuminate\Contracts\Cache\Repository;

/**
 * Each tenant's feature set, cached as plain data in the central store and memoised for the request or job it is bound to.
 */
final class TenantFeatureSetCache
{
    private const int LIFETIME_SECONDS = 600;

    /**
     * @var array<string, FeatureSet>
     */
    private array $memo = [];

    public function __construct(
        #[Cache('central')]
        private readonly Repository $store,
    ) {
    }

    /**
     * @param  Closure(): FeatureSet  $build
     */
    public function remember(string $tenantId, Closure $build): FeatureSet
    {
        return $this->memo[$tenantId] ??= FeatureSet::fromArray(
            $this->store->remember($this->key($tenantId), self::LIFETIME_SECONDS, fn (): array => $build()->toArray()),
        );
    }

    /**
     * @param  list<string>  $tenantIds
     */
    public function forget(array $tenantIds): void
    {
        $this->memo = array_diff_key($this->memo, array_flip($tenantIds));
        $this->store->deleteMultiple(array_map($this->key(...), $tenantIds));
    }

    private function key(string $tenantId): string
    {
        return 'tenant-features:'.$tenantId;
    }
}

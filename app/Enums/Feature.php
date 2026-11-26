<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * The feature keys a plan can include. Each case is added when its feature is built; `features:sync` mirrors them into the features table.
 */
enum Feature: string
{
    /**
     * The unit of the feature's per-plan limit, or null for an on/off feature.
     */
    public function limitUnit(): ?LimitUnit
    {
        return self::limitUnitOf($this->value);
    }

    /**
     * The unit of a stored feature key's limit, or null for an on/off feature or a key the enum doesn't declare.
     */
    public static function limitUnitOf(string $key): ?LimitUnit
    {
        return self::limitUnits()[$key] ?? null;
    }

    /**
     * The features that take a per-plan limit, keyed by feature key. Every other feature is on/off.
     *
     * @return array<string, LimitUnit>
     */
    private static function limitUnits(): array
    {
        return [];
    }
}

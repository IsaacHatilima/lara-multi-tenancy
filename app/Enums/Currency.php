<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * The ISO 4217 currencies a plan can be priced in. Add a case to offer another one.
 */
enum Currency: string
{
    case Eur = 'EUR';
    case Usd = 'USD';
    case Gbp = 'GBP';

    /**
     * @return list<array{value: string, label: string}>
     */
    public static function options(): array
    {
        return array_map(
            static fn (self $currency): array => ['value' => $currency->value, 'label' => $currency->value],
            self::cases(),
        );
    }
}

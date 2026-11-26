<?php

declare(strict_types=1);

namespace App\Data\Billing;

use Illuminate\Support\Number;

final class Money
{
    /**
     * Format an amount stored in minor units, such as cents, for display.
     */
    public static function format(int $amountMinor, string $currency): string
    {
        return (string) Number::currency($amountMinor / 100, in: $currency);
    }
}

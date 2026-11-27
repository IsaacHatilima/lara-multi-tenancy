<?php

declare(strict_types=1);

namespace App\Enums;

enum BillingInterval: string
{
    case Month = 'month';
    case Year = 'year';

    public function label(): string
    {
        return match ($this) {
            self::Month => __('Monthly'),
            self::Year => __('Yearly'),
        };
    }

    /**
     * @return list<array{value: string, label: string}>
     */
    public static function options(): array
    {
        return array_map(
            static fn (self $interval): array => ['value' => $interval->value, 'label' => $interval->label()],
            self::cases(),
        );
    }
}

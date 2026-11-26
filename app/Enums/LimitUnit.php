<?php

declare(strict_types=1);

namespace App\Enums;

enum LimitUnit: string
{
    case Megabytes = 'megabytes';
    case Count = 'count';

    public function label(): string
    {
        return match ($this) {
            self::Megabytes => __('Megabytes'),
            self::Count => __('Count'),
        };
    }
}

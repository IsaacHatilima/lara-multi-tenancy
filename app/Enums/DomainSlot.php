<?php

declare(strict_types=1);

namespace App\Enums;

enum DomainSlot: string
{
    case Primary = 'primary';
    case Secondary = 'secondary';

    public function label(): string
    {
        return match ($this) {
            self::Primary => __('Primary'),
            self::Secondary => __('Secondary'),
        };
    }
}

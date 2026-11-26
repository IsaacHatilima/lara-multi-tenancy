<?php

declare(strict_types=1);

namespace App\Enums;

enum SubscriptionStatus: string
{
    case Trialing = 'trialing';
    case Active = 'active';
    case PastDue = 'past_due';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Trialing => __('Trialing'),
            self::Active => __('Active'),
            self::PastDue => __('Past due'),
            self::Cancelled => __('Cancelled'),
        };
    }
}

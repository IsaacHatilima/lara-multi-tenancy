<?php

declare(strict_types=1);

namespace App\Data\Domains;

final class DomainHostname
{
    /**
     * Normalize a hostname the way it is stored: trimmed, lower case and without a trailing dot.
     */
    public static function normalize(string $hostname): string
    {
        return strtolower(rtrim(trim($hostname), '.'));
    }
}

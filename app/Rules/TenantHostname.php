<?php

declare(strict_types=1);

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Translation\PotentiallyTranslatedString;

final class TenantHostname implements ValidationRule
{
    private const string PATTERN = '/^(?:[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?\.)+[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?$/';

    private const int MAX_LENGTH = 253;

    private const int MAX_LABEL_LENGTH = 63;

    /**
     * @param  list<string>  $centralDomains  Hostnames that belong to the central application.
     */
    public function __construct(
        private readonly array $centralDomains,
    ) {
    }

    /**
     * Normalize a hostname the way it is stored: trimmed, lower case and without a trailing dot.
     */
    public static function normalize(string $hostname): string
    {
        return strtolower(rtrim(trim($hostname), '.'));
    }

    /**
     * @param  Closure(string, ?string=): PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $hostname = self::normalize((string) $value);

        if (strlen($hostname) > self::MAX_LENGTH) {
            $fail($attribute, __('The domain may not be longer than :max characters.', ['max' => self::MAX_LENGTH]));

            return;
        }

        if (max(array_map(strlen(...), explode('.', $hostname))) > self::MAX_LABEL_LENGTH) {
            $fail($attribute, __('Each part of the domain between dots may be at most :max characters.', ['max' => self::MAX_LABEL_LENGTH]));

            return;
        }

        if (preg_match(self::PATTERN, $hostname) !== 1) {
            $fail($attribute, __('Enter a hostname without a scheme, port, or path.'));

            return;
        }

        if (in_array($hostname, $this->centralDomains, true)) {
            $fail($attribute, __('This domain is reserved.'));
        }
    }
}

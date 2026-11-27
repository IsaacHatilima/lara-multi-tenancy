<?php

declare(strict_types=1);

namespace App\Concerns;

use App\Data\Domains\DomainHostname;
use Closure;

trait DomainValidationRules
{
    private const string HOSTNAME_PATTERN = '/^(?:[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?\.)+[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?$/';

    private const int HOSTNAME_MAX_LENGTH = 253;

    private const int HOSTNAME_MAX_LABEL_LENGTH = 63;

    /**
     * Get the validation rules for a company's domain: a bare hostname that isn't one of the central application's.
     *
     * @return list<Closure|string>
     */
    protected function domainRules(): array
    {
        return ['bail', 'required', 'string', $this->validateHostname(...)];
    }

    /**
     * @param  Closure(string, ?string=): mixed  $fail
     */
    private function validateHostname(string $attribute, mixed $value, Closure $fail): void
    {
        $hostname = DomainHostname::normalize((string) $value);

        if (strlen($hostname) > self::HOSTNAME_MAX_LENGTH) {
            $fail($attribute, __('The domain may not be longer than :max characters.', ['max' => self::HOSTNAME_MAX_LENGTH]));

            return;
        }

        if (max(array_map(strlen(...), explode('.', $hostname))) > self::HOSTNAME_MAX_LABEL_LENGTH) {
            $fail($attribute, __('Each part of the domain between dots may be at most :max characters.', ['max' => self::HOSTNAME_MAX_LABEL_LENGTH]));

            return;
        }

        if (preg_match(self::HOSTNAME_PATTERN, $hostname) !== 1) {
            $fail($attribute, __('Enter a hostname without a scheme, port, or path.'));

            return;
        }

        if (in_array($hostname, config('tenancy.identification.central_domains'), true)) {
            $fail($attribute, __('This domain is reserved.'));
        }
    }
}

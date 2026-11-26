<?php

declare(strict_types=1);

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Translation\PotentiallyTranslatedString;
use ResourceBundle;

/**
 * An ISO 4217 currency code that the intl extension knows, in any letter case.
 */
final class CurrencyCode implements ValidationRule
{
    /**
     * @param  Closure(string, ?string=): PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $currencies = ResourceBundle::create('en', 'ICUDATA-curr')?->get('Currencies');

        if (! is_string($value) || ! $currencies instanceof ResourceBundle || $currencies->get(strtoupper($value)) === null) {
            $fail($attribute, __('Enter a three-letter ISO 4217 currency code, such as EUR.'));
        }
    }
}

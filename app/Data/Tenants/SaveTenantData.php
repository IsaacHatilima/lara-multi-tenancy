<?php

declare(strict_types=1);

namespace App\Data\Tenants;

use App\Http\Requests\Tenants\CreateTenantRequest;
use App\Http\Requests\Tenants\UpdateTenantRequest;
use Spatie\LaravelData\Data;

/**
 * A company's own details. Every optional field is sent by the form, so a blank one clears the stored value.
 */
final class SaveTenantData extends Data
{
    public function __construct(
        public readonly string $name,
        public readonly ?string $legalName,
        public readonly ?string $email,
        public readonly ?string $phone,
        public readonly ?string $website,
        public readonly ?string $registrationNumber,
        public readonly ?string $taxNumber,
        public readonly ?string $addressLine1,
        public readonly ?string $addressLine2,
        public readonly ?string $city,
        public readonly ?string $state,
        public readonly ?string $postalCode,
        public readonly ?string $countryCode,
        public readonly ?string $timezone,
        public readonly ?string $locale,
    ) {
    }

    public static function fromRequest(CreateTenantRequest|UpdateTenantRequest $request): self
    {
        $email = self::trimmedOrNull($request, 'email');
        $countryCode = self::trimmedOrNull($request, 'country_code');

        return new self(
            name: trim($request->string('name')->value()),
            legalName: self::trimmedOrNull($request, 'legal_name'),
            email: $email === null ? null : strtolower($email),
            phone: self::trimmedOrNull($request, 'phone'),
            website: self::trimmedOrNull($request, 'website'),
            registrationNumber: self::trimmedOrNull($request, 'registration_number'),
            taxNumber: self::trimmedOrNull($request, 'tax_number'),
            addressLine1: self::trimmedOrNull($request, 'address_line_1'),
            addressLine2: self::trimmedOrNull($request, 'address_line_2'),
            city: self::trimmedOrNull($request, 'city'),
            state: self::trimmedOrNull($request, 'state'),
            postalCode: self::trimmedOrNull($request, 'postal_code'),
            countryCode: $countryCode === null ? null : strtoupper($countryCode),
            timezone: self::trimmedOrNull($request, 'timezone'),
            locale: self::trimmedOrNull($request, 'locale'),
        );
    }

    private static function trimmedOrNull(CreateTenantRequest|UpdateTenantRequest $request, string $key): ?string
    {
        $value = trim($request->string($key)->value());

        return $value === '' ? null : $value;
    }
}

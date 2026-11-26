<?php

declare(strict_types=1);

namespace App\Repositories\Tenants;

use App\Data\Tenants\SaveTenantData;

/**
 * The tenants columns a company's details are stored in, shared by the create and update writes.
 */
final class TenantColumns
{
    /**
     * @return array<string, string|null>
     */
    public static function fromData(SaveTenantData $data): array
    {
        return [
            'name' => $data->name,
            'legal_name' => $data->legalName,
            'email' => $data->email,
            'phone' => $data->phone,
            'website' => $data->website,
            'registration_number' => $data->registrationNumber,
            'tax_number' => $data->taxNumber,
            'address_line_1' => $data->addressLine1,
            'address_line_2' => $data->addressLine2,
            'city' => $data->city,
            'state' => $data->state,
            'postal_code' => $data->postalCode,
            'country_code' => $data->countryCode,
            'timezone' => $data->timezone,
            'locale' => $data->locale,
        ];
    }
}

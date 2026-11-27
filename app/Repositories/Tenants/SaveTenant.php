<?php

declare(strict_types=1);

namespace App\Repositories\Tenants;

use App\Data\Tenants\SaveTenantData;
use App\Models\Tenant;

final readonly class SaveTenant
{
    /**
     * Creates the company when no tenant is given. Creating a tenant also creates, migrates and seeds its
     * database through TenantCreated.
     */
    public function handle(SaveTenantData $data, ?Tenant $tenant = null): Tenant
    {
        $tenant ??= new Tenant();

        $tenant->name = $data->name;
        $tenant->legal_name = $data->legalName;
        $tenant->email = $data->email;
        $tenant->phone = $data->phone;
        $tenant->website = $data->website;
        $tenant->registration_number = $data->registrationNumber;
        $tenant->tax_number = $data->taxNumber;
        $tenant->address_line_1 = $data->addressLine1;
        $tenant->address_line_2 = $data->addressLine2;
        $tenant->city = $data->city;
        $tenant->state = $data->state;
        $tenant->postal_code = $data->postalCode;
        $tenant->country_code = $data->countryCode;
        $tenant->timezone = $data->timezone;
        $tenant->locale = $data->locale;
        $tenant->save();

        return $tenant;
    }
}

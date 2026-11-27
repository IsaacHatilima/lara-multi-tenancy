<?php

declare(strict_types=1);

namespace App\Repositories\Tenants;

use App\Data\Tenants\ComplianceRecord;
use App\Data\Tenants\SaveTenantData;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

final readonly class TenantRepository
{
    private const string TERMS_VERSION = '2026-01';

    private const string DATA_REGION = 'EU (Frankfurt)';

    /**
     * Creates the company when no tenant is given. Creating a tenant also creates, migrates and seeds its
     * database through TenantCreated.
     */
    public function save(SaveTenantData $data, ?Tenant $tenant = null): Tenant
    {
        $tenant ??= new Tenant;

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

    /**
     * Companies by name. `filter[search]` matches their name, email and domains.
     *
     * @return LengthAwarePaginator<int, Tenant>
     */
    public function list(Request $request, int $perPage): LengthAwarePaginator
    {
        $tenants = Tenant::query()
            ->with(['domains' => fn ($query) => $query->select(['id', 'tenant_id', 'domain', 'slot'])]);

        return QueryBuilder::for($tenants, $request)
            ->allowedFilters(
                AllowedFilter::callback('search', fn (Builder $query, string $search) => $query->where(
                    fn (Builder $matches) => $matches
                        ->whereLike('name', '%'.$search.'%')
                        ->orWhereLike('email', '%'.$search.'%')
                        ->orWhereHas('domains', fn (Builder $domains) => $domains->whereLike('domain', '%'.$search.'%')),
                ))->delimiter(''),
            )
            ->orderBy('name')
            ->orderBy('id')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function loadDomains(Tenant $tenant): Tenant
    {
        return $tenant->load(['domains' => fn ($query) => $query->select(['id', 'tenant_id', 'domain', 'slot'])]);
    }

    /**
     * Sample data until accepted agreements have a table: every company accepted the terms when it was created,
     * and roughly half have signed the data processing agreement.
     */
    public function compliance(Tenant $tenant): ComplianceRecord
    {
        $createdAt = $tenant->created_at->toImmutable();

        return new ComplianceRecord(
            termsVersion: self::TERMS_VERSION,
            termsAcceptedAt: $createdAt,
            dataProcessingAgreementSignedAt: crc32((string) $tenant->getTenantKey()) % 2 === 0 ? $createdAt : null,
            dataRegion: self::DATA_REGION,
        );
    }
}

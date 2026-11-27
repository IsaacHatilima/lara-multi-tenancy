<?php

declare(strict_types=1);

namespace App\Models;

use App\Data\Domains\DomainHostname;
use App\Enums\DomainSlot;
use Database\Factories\DomainFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Carbon;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;
use Stancl\Tenancy\Database\Models\Domain as BaseDomain;

/**
 * @property int $id
 * @property string $domain
 * @property string $tenant_id
 * @property DomainSlot $slot
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @property-read Tenant $tenant
 */
#[Fillable(['domain', 'slot'])]
class Domain extends BaseDomain
{
    /** @use HasFactory<DomainFactory> */
    use HasFactory;

    use LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['domain', 'slot', 'tenant_id'])
            ->logOnlyDirty();
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'slot' => DomainSlot::class,
        ];
    }

    /**
     * @return Attribute<string|null, string|null>
     */
    protected function domain(): Attribute
    {
        return Attribute::make(
            set: fn (?string $value): ?string => $value === null ? null : DomainHostname::normalize($value),
        );
    }
}

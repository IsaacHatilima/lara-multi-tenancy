<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\BillingInterval;
use App\Enums\PlanDeployment;
use App\Enums\PlanStatus;
use Database\Factories\PlanFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Stancl\Tenancy\Database\Concerns\CentralConnection;

/**
 * One version of a plan. A code keeps one active version; a custom plan belongs to the one tenant it was arranged for.
 *
 * @property string $id
 * @property string $code
 * @property string $name
 * @property string|null $description
 * @property PlanStatus $status
 * @property PlanDeployment $deployment
 * @property bool $is_public
 * @property bool $is_default
 * @property string|null $tenant_id
 * @property int $price_minor
 * @property string $currency
 * @property BillingInterval $interval
 * @property int $trial_days
 * @property int $sort_order
 * @property int|null $created_by
 * @property int|null $updated_by
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @property-read Tenant|null $tenant
 */
#[Fillable(['code', 'name', 'status', 'deployment', 'is_public', 'is_default', 'price_minor', 'currency', 'interval', 'sort_order'])]
class Plan extends Model
{
    use CentralConnection;

    /** @use HasFactory<PlanFactory> */
    use HasFactory;

    use HasUuids;

    /**
     * @return BelongsTo<Tenant, $this>
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => PlanStatus::class,
            'deployment' => PlanDeployment::class,
            'is_public' => 'boolean',
            'is_default' => 'boolean',
            'interval' => BillingInterval::class,
        ];
    }
}

<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\BillingInterval;
use App\Enums\Currency;
use Carbon\CarbonImmutable;
use Database\Factories\PlanFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;
use Stancl\Tenancy\Database\Concerns\CentralConnection;

/**
 * A plan, with the features it includes.
 *
 * @property string $id
 * @property string $name
 * @property string $code
 * @property string|null $description
 * @property int $price_minor
 * @property Currency $currency
 * @property BillingInterval $interval
 * @property int $trial_days
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 *
 * @property-read Collection<int, Feature> $features
 */
#[Fillable(['name', 'code', 'description', 'price_minor', 'currency', 'interval', 'trial_days'])]
class Plan extends Model
{
    use CentralConnection;

    /** @use HasFactory<PlanFactory> */
    use HasFactory;

    use HasUuids;
    use LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontLogEmptyChanges();
    }

    /**
     * The pivot's limit caps the feature on this plan; null means unlimited.
     *
     * @return BelongsToMany<Feature, $this>
     */
    public function features(): BelongsToMany
    {
        return $this->belongsToMany(Feature::class, 'plan_feature')
            ->withPivot('limit')
            ->withTimestamps();
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'price_minor' => 'integer',
            'currency' => Currency::class,
            'interval' => BillingInterval::class,
            'trial_days' => 'integer',
        ];
    }
}

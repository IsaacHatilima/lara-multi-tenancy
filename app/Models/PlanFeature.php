<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;
use Stancl\Tenancy\Database\Concerns\CentralConnection;

/**
 * A feature on a plan. Its limit caps the feature on that plan; null means unlimited. The table has its own
 * incrementing id, so activity on a row can point at it.
 *
 * @property int $id
 * @property string $plan_id
 * @property int $feature_id
 * @property int|null $limit
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 *
 * @property-read Feature $feature
 */
#[Table(name: 'plan_feature', incrementing: true)]
final class PlanFeature extends Pivot
{
    use CentralConnection;
    use LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['plan_id', 'feature_id', 'limit'])
            ->logOnlyDirty()
            ->dontLogEmptyChanges();
    }

    /**
     * @return BelongsTo<Feature, $this>
     */
    public function feature(): BelongsTo
    {
        return $this->belongsTo(Feature::class);
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'feature_id' => 'integer',
            'limit' => 'integer',
        ];
    }
}

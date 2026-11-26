<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\PlanFeatureFactory;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Support\Carbon;
use Stancl\Tenancy\Database\Concerns\CentralConnection;

/**
 * A plan includes a feature. An empty limit means unlimited; a plan without the row doesn't include the feature.
 *
 * @property int $id
 * @property string $plan_id
 * @property int $feature_id
 * @property int|null $limit
 * @property int|null $updated_by
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Table(incrementing: true)]
class PlanFeature extends Pivot
{
    use CentralConnection;

    /** @use HasFactory<PlanFeatureFactory> */
    use HasFactory;
}

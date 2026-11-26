<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\CarbonImmutable;
use Database\Factories\FeatureFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Stancl\Tenancy\Database\Concerns\CentralConnection;

/**
 * A row per feature key. The key is a `Feature` enum value, kept as a string so rows the enum no longer has stay readable.
 *
 * @property int $id
 * @property string $key
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 *
 * @property-read Collection<int, Plan> $plans
 */
#[Fillable(['key'])]
class Feature extends Model
{
    use CentralConnection;

    /** @use HasFactory<FeatureFactory> */
    use HasFactory;

    /**
     * @return BelongsToMany<Plan, $this, PlanFeature>
     */
    public function plans(): BelongsToMany
    {
        return $this->belongsToMany(Plan::class, PlanFeature::class);
    }
}

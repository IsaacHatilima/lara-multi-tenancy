<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\CarbonImmutable;
use Database\Factories\PlanFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Stancl\Tenancy\Database\Concerns\CentralConnection;

/**
 * A plan, with the features it includes.
 *
 * @property string $id
 * @property string $name
 * @property string $code
 * @property string|null $description
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 *
 * @property-read Collection<int, Feature> $features
 */
#[Fillable(['name', 'code', 'description'])]
class Plan extends Model
{
    use CentralConnection;

    /** @use HasFactory<PlanFactory> */
    use HasFactory;

    use HasUuids;

    /**
     * @return BelongsToMany<Feature, $this, PlanFeature>
     */
    public function features(): BelongsToMany
    {
        return $this->belongsToMany(Feature::class, PlanFeature::class)
            ->withPivot('limit', 'updated_by')
            ->withTimestamps();
    }
}

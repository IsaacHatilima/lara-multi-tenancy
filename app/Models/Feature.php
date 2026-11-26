<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\LimitUnit;
use Carbon\CarbonImmutable;
use Database\Factories\FeatureFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Stancl\Tenancy\Database\Concerns\CentralConnection;

/**
 * Something a plan can include. Code checks a feature by its key, which never changes. A feature without a limit unit
 * is on or off; one with a unit can be capped per plan.
 *
 * @property int $id
 * @property string $key
 * @property string $name
 * @property string|null $description
 * @property LimitUnit|null $limit_unit
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 */
#[Fillable(['key', 'name', 'description', 'limit_unit'])]
class Feature extends Model
{
    use CentralConnection;

    /** @use HasFactory<FeatureFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'limit_unit' => LimitUnit::class,
        ];
    }
}

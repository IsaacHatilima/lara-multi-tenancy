<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\FeatureFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Stancl\Tenancy\Database\Concerns\CentralConnection;

/**
 * A row per feature key. The key is a `Feature` enum value, kept as a string so rows the enum no longer has stay readable.
 *
 * @property int $id
 * @property string $key
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['key'])]
class Feature extends Model
{
    use CentralConnection;

    /** @use HasFactory<FeatureFactory> */
    use HasFactory;
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\Pivot;

class PlanFeature extends Pivot
{
    /** @use HasFactory<\Database\Factories\PlanFeatureFactory> */
    use HasFactory;
}

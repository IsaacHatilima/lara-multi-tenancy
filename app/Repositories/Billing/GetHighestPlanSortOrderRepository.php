<?php

declare(strict_types=1);

namespace App\Repositories\Billing;

use App\Models\Plan;

final readonly class GetHighestPlanSortOrderRepository
{
    /**
     * The highest display order across all plans, or null when there are none.
     */
    public function handle(): ?int
    {
        $sortOrder = Plan::query()->max('sort_order');

        return $sortOrder === null ? null : (int) $sortOrder;
    }
}

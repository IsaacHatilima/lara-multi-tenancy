<?php

declare(strict_types=1);

namespace App\Actions\Plans;

use App\Repositories\Billing\GetHighestPlanSortOrderRepository;

final readonly class GetNextPlanSortOrderAction
{
    public function __construct(
        private GetHighestPlanSortOrderRepository $highestSortOrder,
    ) {
    }

    /**
     * A new plan's default display order: after the last plan, or first when there are none.
     */
    public function handle(): int
    {
        $highest = $this->highestSortOrder->handle();

        return $highest === null ? 0 : $highest + 1;
    }
}

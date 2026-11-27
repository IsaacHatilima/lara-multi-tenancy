<?php

declare(strict_types=1);

namespace App\Actions\Plans;

use App\Data\Plans\PlanResource;
use App\Http\Requests\Plans\ListPlansRequest;
use App\Models\Plan;
use App\Repositories\Plans\PlanRepository;
use Illuminate\Pagination\LengthAwarePaginator;

final readonly class ListPlansAction
{
    private const int PER_PAGE = 15;

    public function __construct(
        private PlanRepository $plans,
    ) {
    }

    /**
     * @return LengthAwarePaginator<int, PlanResource>
     */
    public function handle(ListPlansRequest $request): LengthAwarePaginator
    {
        return $this->plans->list($request, self::PER_PAGE)
            ->through(fn (Plan $plan): PlanResource => PlanResource::fromModel($plan));
    }
}

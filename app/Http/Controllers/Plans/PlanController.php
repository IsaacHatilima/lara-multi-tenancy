<?php

declare(strict_types=1);

namespace App\Http\Controllers\Plans;

use App\Actions\Plans\GetNextPlanSortOrderAction;
use App\Actions\Plans\ListPlansAction;
use App\Enums\BillingInterval;
use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;

final class PlanController extends Controller
{
    /**
     * List every hosted plan, public and custom, active and inactive.
     */
    public function index(ListPlansAction $listPlans): Response
    {
        return Inertia::render('plans/index', [
            'plans' => $listPlans->handle(),
        ]);
    }

    /**
     * Show the form for a new plan, with the plans it can start from.
     */
    public function create(ListPlansAction $listPlans, GetNextPlanSortOrderAction $getNextSortOrder): Response
    {
        return Inertia::render('plans/create', [
            'plans' => $listPlans->handle(),
            'intervals' => BillingInterval::options(),
            'defaultSortOrder' => $getNextSortOrder->handle(),
        ]);
    }
}

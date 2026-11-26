<?php

declare(strict_types=1);

namespace App\Http\Controllers\Plans;

use App\Actions\Plans\CreatePlanAction;
use App\Actions\Plans\GetNextPlanSortOrderAction;
use App\Actions\Plans\GetPlanAction;
use App\Actions\Plans\ListPlansAction;
use App\Data\Plans\CreatePlanData;
use App\Enums\BillingInterval;
use App\Http\Controllers\Controller;
use App\Http\Requests\Plans\CreatePlanRequest;
use App\Models\Plan;
use App\Models\User;
use Illuminate\Container\Attributes\CurrentUser;
use Illuminate\Http\RedirectResponse;
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

    /**
     * Create an active hosted plan, recorded as created by the signed-in user.
     */
    public function store(CreatePlanRequest $request, #[CurrentUser] User $user, CreatePlanAction $createPlan): RedirectResponse
    {
        $createPlan->handle(CreatePlanData::fromRequest($request), $user);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Plan created.')]);

        return to_route('plans.index');
    }

    /**
     * Show the form for a plan's own fields and the features it includes, with every feature row to choose from.
     */
    public function edit(Plan $plan, GetPlanAction $getPlan): Response
    {
        return Inertia::render('plans/edit', [
            'plan' => $getPlan->handle($plan),
        ]);
    }
}

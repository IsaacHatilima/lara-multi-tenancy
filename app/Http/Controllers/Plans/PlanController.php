<?php

declare(strict_types=1);

namespace App\Http\Controllers\Plans;

use App\Actions\Plans\CreatePlanAction;
use App\Actions\Plans\DeletePlanAction;
use App\Actions\Plans\GetPlanAction;
use App\Actions\Plans\ListPlanFeaturesAction;
use App\Actions\Plans\ListPlansAction;
use App\Actions\Plans\UpdatePlanAction;
use App\Data\Plans\CreatePlanData;
use App\Data\Plans\UpdatePlanData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Plans\CreatePlanRequest;
use App\Http\Requests\Plans\UpdatePlanRequest;
use App\Models\Plan;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

final class PlanController extends Controller
{
    public function index(ListPlansAction $listPlans): Response
    {
        return Inertia::render('plans/index', [
            'plans' => $listPlans->handle(),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('plans/create');
    }

    /**
     * Creates the plan and opens it, where its features are chosen.
     */
    public function store(CreatePlanRequest $request, CreatePlanAction $createPlan): RedirectResponse
    {
        $plan = $createPlan->handle(CreatePlanData::fromRequest($request));

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Plan created.')]);

        return to_route('plans.edit', $plan);
    }

    public function edit(Plan $plan, GetPlanAction $getPlan, ListPlanFeaturesAction $listPlanFeatures): Response
    {
        return Inertia::render('plans/edit', [
            'plan' => $getPlan->handle($plan),
            'features' => $listPlanFeatures->handle($plan),
        ]);
    }

    public function update(UpdatePlanRequest $request, Plan $plan, UpdatePlanAction $updatePlan): RedirectResponse
    {
        $updatePlan->handle($plan, UpdatePlanData::fromRequest($request));

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Plan updated.')]);

        return to_route('plans.edit', $plan);
    }

    public function destroy(Plan $plan, DeletePlanAction $deletePlan): RedirectResponse
    {
        $deletePlan->handle($plan);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Plan deleted.')]);

        return to_route('plans.index');
    }
}

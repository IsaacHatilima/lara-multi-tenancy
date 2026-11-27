<?php

declare(strict_types=1);

namespace App\Http\Controllers\Plans;

use App\Actions\Plans\AddPlanFeatureAction;
use App\Actions\Plans\RemovePlanFeatureAction;
use App\Actions\Plans\SearchPlanFeaturesAction;
use App\Actions\Plans\UpdatePlanFeatureAction;
use App\Data\Plans\AddPlanFeatureData;
use App\Data\Plans\SearchPlanFeaturesData;
use App\Data\Plans\UpdatePlanFeatureData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Plans\AddPlanFeatureRequest;
use App\Http\Requests\Plans\SearchPlanFeaturesRequest;
use App\Http\Requests\Plans\UpdatePlanFeatureRequest;
use App\Models\Feature;
use App\Models\Plan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use InertiaUI\Notifications\Toast;

final class PlanFeatureController extends Controller
{
    /**
     * Features the plan doesn't include yet, as JSON for the add-feature search.
     */
    public function search(SearchPlanFeaturesRequest $request, Plan $plan, SearchPlanFeaturesAction $searchFeatures): JsonResponse
    {
        return response()->json($searchFeatures->handle($plan, SearchPlanFeaturesData::fromRequest($request)));
    }

    public function store(AddPlanFeatureRequest $request, Plan $plan, AddPlanFeatureAction $addFeature): RedirectResponse
    {
        $addFeature->handle($plan, AddPlanFeatureData::fromRequest($request));

        Toast::success('Feature added.')->flash();

        return to_route('plans.edit', $plan);
    }

    public function update(UpdatePlanFeatureRequest $request, Plan $plan, Feature $feature, UpdatePlanFeatureAction $updateFeature): RedirectResponse
    {
        $updateFeature->handle($plan, $feature, UpdatePlanFeatureData::fromRequest($request));

        Toast::success('Limit saved.')->flash();

        return to_route('plans.edit', $plan);
    }

    public function destroy(Plan $plan, Feature $feature, RemovePlanFeatureAction $removeFeature): RedirectResponse
    {
        $removeFeature->handle($plan, $feature);

        Toast::success('Feature removed.')->flash();

        return to_route('plans.edit', $plan);
    }
}

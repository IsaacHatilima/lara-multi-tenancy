<?php

declare(strict_types=1);

namespace App\Http\Controllers\Features;

use App\Actions\Features\CreateFeatureAction;
use App\Actions\Features\DeleteFeatureAction;
use App\Actions\Features\GetFeatureAction;
use App\Actions\Features\ListFeaturesAction;
use App\Actions\Features\UpdateFeatureAction;
use App\Data\Features\CreateFeatureData;
use App\Data\Features\ListFeaturesData;
use App\Data\Features\UpdateFeatureData;
use App\Enums\LimitUnit;
use App\Http\Controllers\Controller;
use App\Http\Requests\Features\CreateFeatureRequest;
use App\Http\Requests\Features\ListFeaturesRequest;
use App\Http\Requests\Features\UpdateFeatureRequest;
use App\Models\Feature;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;
use InertiaUI\Notifications\Toast;

final class FeatureController extends Controller
{
    public function index(ListFeaturesRequest $request, ListFeaturesAction $listFeatures): Response
    {
        return Inertia::render('features/index', [
            'features' => $listFeatures->handle($request),
            'filters' => ['search' => ListFeaturesData::fromRequest($request)->search],
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('features/create', [
            'limitUnits' => LimitUnit::options(),
        ]);
    }

    public function store(CreateFeatureRequest $request, CreateFeatureAction $createFeature): RedirectResponse
    {
        $createFeature->handle(CreateFeatureData::fromRequest($request));

        Toast::success('Feature created.')
            ->description('Include it in a plan from that plan\'s edit page.')
            ->flash();

        return to_route('features.index');
    }

    public function edit(Feature $feature, GetFeatureAction $getFeature): Response
    {
        return Inertia::render('features/edit', [
            'feature' => $getFeature->handle($feature),
            'limitUnits' => LimitUnit::options(),
        ]);
    }

    public function update(UpdateFeatureRequest $request, Feature $feature, UpdateFeatureAction $updateFeature): RedirectResponse
    {
        $updateFeature->handle($feature, UpdateFeatureData::fromRequest($request));

        Toast::success('Feature updated.')->flash();

        return to_route('features.index');
    }

    public function destroy(Feature $feature, DeleteFeatureAction $deleteFeature): RedirectResponse
    {
        $deleteFeature->handle($feature);

        Toast::success('Feature deleted.')
            ->description('It was removed from every plan that included it.')
            ->flash();

        return to_route('features.index');
    }
}

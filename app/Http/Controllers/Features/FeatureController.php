<?php

declare(strict_types=1);

namespace App\Http\Controllers\Features;

use App\Actions\Features\ListFeaturesAction;
use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;

final class FeatureController extends Controller
{
    /**
     * List every feature row with its limit unit and the active plans that include it.
     */
    public function index(ListFeaturesAction $listFeatures): Response
    {
        return Inertia::render('features/index', [
            'features' => $listFeatures->handle(),
        ]);
    }
}

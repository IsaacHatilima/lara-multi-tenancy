<?php

declare(strict_types=1);

namespace App\Actions\Features;

use App\Data\Features\FeatureResource;
use App\Http\Requests\Features\ListFeaturesRequest;
use App\Models\Feature;
use App\Repositories\Features\FeatureRepository;
use Illuminate\Pagination\LengthAwarePaginator;

final readonly class ListFeaturesAction
{
    private const int PER_PAGE = 15;

    public function __construct(
        private FeatureRepository $features,
    ) {
    }

    /**
     * @return LengthAwarePaginator<int, FeatureResource>
     */
    public function handle(ListFeaturesRequest $request): LengthAwarePaginator
    {
        return $this->features->list($request, self::PER_PAGE)
            ->through(fn (Feature $feature): FeatureResource => FeatureResource::fromModel($feature));
    }
}

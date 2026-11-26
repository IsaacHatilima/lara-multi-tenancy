<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Actions\Features\GetFeatureSetAction;
use App\Http\Responses\FeatureUnavailableResponse;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Refuses creating and changing what the host's plan doesn't include. Reads and deletes pass, so a tenant keeps its data.
 */
final readonly class EnsureFeatureIsAvailable
{
    public function __construct(
        private GetFeatureSetAction $getFeatureSet,
    ) {
    }

    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next, string $feature): Response
    {
        if (! in_array($request->method(), ['POST', 'PUT', 'PATCH'], true)
            || $this->getFeatureSet->handle($request->getHost(), tenant())->isEnabled($feature)) {
            return $next($request);
        }

        return FeatureUnavailableResponse::feature($feature)->toResponse($request);
    }
}

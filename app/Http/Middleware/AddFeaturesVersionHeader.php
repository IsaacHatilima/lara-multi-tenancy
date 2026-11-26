<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Actions\Features\GetFeatureSetAction;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Stamps a signed-in user's responses, refusals included, with the version of the host's features, so a client can resync.
 */
final readonly class AddFeaturesVersionHeader
{
    public function __construct(
        private GetFeatureSetAction $getFeatureSet,
    ) {
    }

    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if ($request->user() !== null) {
            $response->headers->set('X-Features-Version', $this->getFeatureSet->handle($request->getHost(), tenant())->version());
        }

        return $response;
    }
}

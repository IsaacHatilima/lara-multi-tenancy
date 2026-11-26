<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Actions\Features\GetFeatureSetAction;
use App\Http\Responses\FeatureUnavailableResponse;
use Closure;
use Illuminate\Container\Attributes\Config;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Refuses creating and changing anything while the tenant is read-only, except on the routes `billing.read_only_exempt` names.
 * Reads and deletes pass, so the tenant can still see its data, export it and close its account.
 */
final readonly class EnforceReadOnlyMode
{
    /**
     * @param  list<string>  $exemptRoutes
     */
    public function __construct(
        private GetFeatureSetAction $getFeatureSet,
        #[Config('billing.read_only_exempt')]
        private array $exemptRoutes,
    ) {
    }

    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! in_array($request->method(), ['POST', 'PUT', 'PATCH'], true)
            || in_array($request->route()?->getName(), $this->exemptRoutes, true)
            || ! $this->getFeatureSet->handle($request->getHost(), tenant())->readOnly) {
            return $next($request);
        }

        return FeatureUnavailableResponse::readOnly()->toResponse($request);
    }
}

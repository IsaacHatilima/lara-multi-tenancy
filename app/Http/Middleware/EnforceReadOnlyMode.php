<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Actions\Features\GetFeatureSetAction;
use App\Actions\Features\IsFeatureOnDefaultPlanAction;
use App\Http\Responses\FeatureUnavailableResponse;
use Closure;
use Illuminate\Container\Attributes\Config;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

/**
 * Refuses creating and changing anything while the tenant is read-only, except on the routes `billing.read_only_exempt` names
 * and on routes of features the default plan includes. Reads and deletes pass, so the tenant can still see its data, export it and close its account.
 */
final readonly class EnforceReadOnlyMode
{
    /**
     * @param  list<string>  $exemptRoutes
     */
    public function __construct(
        private GetFeatureSetAction $getFeatureSet,
        private IsFeatureOnDefaultPlanAction $isFeatureOnDefaultPlan,
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
            || in_array($request->route()->getName(), $this->exemptRoutes, true)
            || ! $this->getFeatureSet->handle($request->getHost(), tenant())->readOnly
            || $this->isDefaultPlanFeatureRoute($request->route())) {
            return $next($request);
        }

        return FeatureUnavailableResponse::readOnly()->toResponse($request);
    }

    /**
     * Whether the route carries `feature:{key}` for a feature the default plan includes.
     */
    private function isDefaultPlanFeatureRoute(Route $route): bool
    {
        $features = array_map(
            fn (string $middleware): string => Str::after($middleware, 'feature:'),
            array_filter($route->gatherMiddleware(), fn (mixed $middleware): bool => is_string($middleware) && str_starts_with($middleware, 'feature:')),
        );

        return array_any($features, fn (string $feature): bool => $this->isFeatureOnDefaultPlan->handle($feature));
    }
}

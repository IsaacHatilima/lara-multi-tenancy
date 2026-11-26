<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Actions\Tenants\IsTenantManagementAvailableAction;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Answers 404 outside the central host of a managed deployment, so tenant hosts can't discover the routes.
 */
final readonly class EnsureTenantManagementIsAvailable
{
    public function __construct(
        private IsTenantManagementAvailableAction $isTenantManagementAvailable,
    ) {
    }

    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        abort_unless($this->isTenantManagementAvailable->handle($request->getHost()), Response::HTTP_NOT_FOUND);

        return $next($request);
    }
}

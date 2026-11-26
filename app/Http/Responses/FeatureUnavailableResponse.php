<?php

declare(strict_types=1);

namespace App\Http\Responses;

use Illuminate\Contracts\Support\Responsable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Support\Header;
use Symfony\Component\HttpFoundation\Response;

/**
 * Refuses a write the tenant's plan or read-only account doesn't allow. Inertia forms go back with an error and a toast,
 * like a failed validation, so the next page carries the fresh feature set.
 */
final readonly class FeatureUnavailableResponse implements Responsable
{
    private function __construct(
        private ?string $feature,
        private string $message,
    ) {
    }

    public static function feature(string $feature): self
    {
        return new self($feature, __("Your plan doesn't include this feature."));
    }

    public static function readOnly(): self
    {
        return new self(null, __('This account is read-only because its payment is overdue. You can still view and delete existing data.'));
    }

    /**
     * @param  Request  $request
     */
    public function toResponse($request): Response
    {
        if ($request->header(Header::INERTIA)) {
            Inertia::flash('toast', ['type' => 'error', 'message' => $this->message]);

            return back()->withErrors(['feature_unavailable' => $this->message]);
        }

        if ($request->expectsJson()) {
            return new JsonResponse([
                'code' => 'feature_unavailable',
                'feature' => $this->feature,
                'message' => $this->message,
            ], Response::HTTP_FORBIDDEN);
        }

        abort(Response::HTTP_FORBIDDEN, $this->message);
    }
}

<?php

declare(strict_types=1);

namespace App\Data\Plans;

use App\Enums\BillingInterval;
use App\Http\Requests\Plans\CreatePlanRequest;
use Spatie\LaravelData\Data;

/**
 * A new hosted plan as staff typed it, optionally copying the features and limits of the plan it starts from.
 */
final class CreatePlanData extends Data
{
    public function __construct(
        public readonly string $code,
        public readonly string $name,
        public readonly ?string $description,
        public readonly int $priceMinor,
        public readonly string $currency,
        public readonly BillingInterval $interval,
        public readonly int $trialDays,
        public readonly bool $isPublic,
        public readonly int $sortOrder,
        public readonly ?string $startFromPlanId,
    ) {
    }

    public static function fromRequest(CreatePlanRequest $request): self
    {
        $description = trim($request->string('description')->value());
        $startFromPlanId = $request->string('start_from')->value();

        return new self(
            code: trim($request->string('code')->value()),
            name: trim($request->string('name')->value()),
            description: $description === '' ? null : $description,
            priceMinor: self::toMinorUnits(trim($request->string('price')->value())),
            currency: strtoupper(trim($request->string('currency')->value())),
            interval: BillingInterval::from($request->string('interval')->value()),
            trialDays: $request->integer('trial_days'),
            isPublic: $request->boolean('is_public'),
            sortOrder: $request->integer('sort_order'),
            startFromPlanId: $startFromPlanId === '' ? null : $startFromPlanId,
        );
    }

    /**
     * Splits the validated price, which has at most two decimals, at its point, so no floating-point rounding is involved.
     */
    private static function toMinorUnits(string $price): int
    {
        [$units, $cents] = array_pad(explode('.', $price, 2), 2, '');

        return (int) $units * 100 + (int) str_pad($cents, 2, '0');
    }
}

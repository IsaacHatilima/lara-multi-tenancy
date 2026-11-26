<?php

declare(strict_types=1);

namespace App\Actions\Domains;

use App\Enums\DomainSlot;
use App\Models\Domain;
use App\Models\Tenant;
use App\Repositories\Domains\ListOccupiedDomainSlotsRepository;
use Illuminate\Validation\ValidationException;

/**
 * Decides which of a company's two slots a domain takes: the requested one when it is free, otherwise the first free one.
 */
final readonly class ResolveDomainSlotAction
{
    public function __construct(
        private ListOccupiedDomainSlotsRepository $occupiedSlots,
    ) {
    }

    public function handle(Tenant $tenant, ?DomainSlot $requested, ?Domain $current = null): DomainSlot
    {
        $occupied = $this->occupiedSlots->handle($tenant, $current);
        $available = array_values(array_filter(
            DomainSlot::cases(),
            fn (DomainSlot $slot): bool => ! in_array($slot, $occupied, true),
        ));

        if ($available === []) {
            throw ValidationException::withMessages([
                'domain' => __('A company may have at most two domains.'),
            ]);
        }

        if ($requested !== null && ! in_array($requested, $available, true)) {
            throw ValidationException::withMessages([
                'slot' => __('This domain slot is already occupied for the company.'),
            ]);
        }

        return $requested ?? $available[0];
    }
}

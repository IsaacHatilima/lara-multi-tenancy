<?php

declare(strict_types=1);

namespace App\Data\Domains;

use App\Enums\DomainSlot;
use App\Http\Requests\Domains\SaveDomainRequest;
use Spatie\LaravelData\Data;

final class SaveDomainData extends Data
{
    public function __construct(
        public readonly string $hostname,
        public readonly ?DomainSlot $slot,
    ) {
    }

    public static function fromRequest(SaveDomainRequest $request): self
    {
        return new self(
            hostname: DomainHostname::normalize($request->string('domain')->value()),
            slot: $request->enum('slot', DomainSlot::class),
        );
    }
}

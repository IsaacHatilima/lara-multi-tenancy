<?php

declare(strict_types=1);

namespace App\Repositories\Domains;

use App\Models\Domain;

final readonly class DeleteDomain
{
    public function handle(Domain $domain): void
    {
        $domain->delete();
    }
}

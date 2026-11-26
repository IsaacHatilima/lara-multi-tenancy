<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Actions\Features\SyncFeaturesAction;
use App\Enums\Feature;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('features:sync')]
#[Description('Add a features row for every Feature enum case and report rows the enum no longer has')]
final class SyncFeaturesCommand extends Command
{
    public function handle(SyncFeaturesAction $syncFeatures): int
    {
        $unknownKeys = $syncFeatures->handle(array_map(fn (Feature $feature): string => $feature->value, Feature::cases()));

        foreach ($unknownKeys as $key) {
            $this->warn(__('The feature :key is stored but not in the Feature enum. Its row and plan mappings were kept.', ['key' => $key]));
        }

        return self::SUCCESS;
    }
}

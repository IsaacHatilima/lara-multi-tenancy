<?php

declare(strict_types=1);

namespace App\Observers;

use App\Actions\Features\ClearPlanTenantsFeaturesAction;
use App\Enums\PlanDeployment;
use App\Models\Plan;
use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;

/**
 * A plan change reaches its subscribers' feature sets, and every unsubscribed tenant's when the plan is or was the hosted default.
 * Deleting a plan removes its mappings in the database without their events, so the delete clears here too.
 */
final readonly class PlanObserver implements ShouldHandleEventsAfterCommit
{
    public function __construct(
        private ClearPlanTenantsFeaturesAction $clearPlanTenantsFeatures,
    ) {
    }

    public function updated(Plan $plan): void
    {
        $this->clearPlanTenantsFeatures->handle($plan->id, $this->wasDefault($plan));
    }

    public function deleted(Plan $plan): void
    {
        $this->clearPlanTenantsFeatures->handle($plan->id, $this->wasDefault($plan));
    }

    /**
     * After commit the model has already synced its original values, so a changed flag stands in for the old one.
     */
    private function wasDefault(Plan $plan): bool
    {
        return ($plan->is_default || $plan->wasChanged('is_default'))
            && ($plan->deployment === PlanDeployment::Hosted || $plan->wasChanged('deployment'));
    }
}

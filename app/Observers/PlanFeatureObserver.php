<?php

declare(strict_types=1);

namespace App\Observers;

use App\Actions\Features\ClearPlanTenantsFeaturesAction;
use App\Models\PlanFeature;
use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;

/**
 * A plan gaining, losing or re-limiting a feature reaches its tenants' feature sets, including writes made through the plan's
 * features relation, which saves and deletes this pivot model.
 */
final readonly class PlanFeatureObserver implements ShouldHandleEventsAfterCommit
{
    public function __construct(
        private ClearPlanTenantsFeaturesAction $clearPlanTenantsFeatures,
    ) {
    }

    public function created(PlanFeature $planFeature): void
    {
        $this->clearPlanTenantsFeatures->handle($planFeature->plan_id, false);
    }

    public function updated(PlanFeature $planFeature): void
    {
        $this->clearPlanTenantsFeatures->handle($planFeature->plan_id, false);
    }

    public function deleted(PlanFeature $planFeature): void
    {
        $this->clearPlanTenantsFeatures->handle($planFeature->plan_id, false);
    }
}

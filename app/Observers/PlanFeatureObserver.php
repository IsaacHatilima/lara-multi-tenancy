<?php

namespace App\Observers;

use App\Models\PlanFeature;

class PlanFeatureObserver
{
    /**
     * Handle the PlanFeature "created" event.
     */
    public function created(PlanFeature $planFeature): void
    {
        //
    }

    /**
     * Handle the PlanFeature "updated" event.
     */
    public function updated(PlanFeature $planFeature): void
    {
        //
    }

    /**
     * Handle the PlanFeature "deleted" event.
     */
    public function deleted(PlanFeature $planFeature): void
    {
        //
    }

    /**
     * Handle the PlanFeature "restored" event.
     */
    public function restored(PlanFeature $planFeature): void
    {
        //
    }

    /**
     * Handle the PlanFeature "force deleted" event.
     */
    public function forceDeleted(PlanFeature $planFeature): void
    {
        //
    }
}

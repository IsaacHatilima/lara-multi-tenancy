<?php

namespace App\Observers;

use App\Models\Plan;

class PlanObserver
{
    /**
     * Handle the Plan "updated" event.
     */
    public function updated(Plan $plan): void
    {
        //
    }

    /**
     * Handle the Plan "deleted" event.
     */
    public function deleted(Plan $plan): void
    {
        //
    }
}

<?php

declare(strict_types=1);

return [
    'managed_deployment' => (bool) env('MITARBO_MANAGED_DEPLOYMENT', true),
    'license_grace_period_days' => (int) env('MITARBO_LICENSE_GRACE_PERIOD_DAYS', 14),
];

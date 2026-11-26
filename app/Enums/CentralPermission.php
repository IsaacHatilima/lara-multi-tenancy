<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * Permissions of central platform staff, stored only in the central database under the web guard.
 */
enum CentralPermission: string
{
    case ManagePlans = 'plans.manage';
}

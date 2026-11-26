<?php

declare(strict_types=1);

namespace App\Enums;

enum PlanDeployment: string
{
    case Hosted = 'hosted';
    case SelfHosted = 'self_hosted';
}

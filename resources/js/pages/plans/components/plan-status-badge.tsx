import { CircleCheckIcon, CircleSlashIcon } from 'lucide-react';
import type { LucideIcon } from 'lucide-react';
import { Badge } from '@/components/ui/badge';
import type { Plan } from '../types';

const STATUS_STYLES: Record<
    Plan['status'],
    { variant: 'secondary' | 'outline'; icon: LucideIcon }
> = {
    active: { variant: 'secondary', icon: CircleCheckIcon },
    inactive: { variant: 'outline', icon: CircleSlashIcon },
};

export function PlanStatusBadge({
    plan,
}: {
    plan: Pick<Plan, 'status' | 'statusLabel'>;
}) {
    const { variant, icon: Icon } = STATUS_STYLES[plan.status];

    return (
        <Badge variant={variant}>
            <Icon data-icon="inline-start" />
            {plan.statusLabel}
        </Badge>
    );
}

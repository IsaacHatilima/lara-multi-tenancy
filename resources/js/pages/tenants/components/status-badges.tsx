import {
    CircleAlertIcon,
    CircleCheckIcon,
    CircleDashedIcon,
    CircleSlashIcon,
    RotateCcwIcon,
} from 'lucide-react';
import type { LucideIcon } from 'lucide-react';
import { Badge } from '@/components/ui/badge';
import type { Payment, Subscription } from '../types';

type BadgeStyle = {
    variant: 'secondary' | 'outline' | 'destructive';
    icon: LucideIcon;
};

const SUBSCRIPTION_STYLES: Record<Subscription['status'], BadgeStyle> = {
    active: { variant: 'secondary', icon: CircleCheckIcon },
    trialing: { variant: 'outline', icon: CircleDashedIcon },
    past_due: { variant: 'destructive', icon: CircleAlertIcon },
    read_only: { variant: 'destructive', icon: CircleAlertIcon },
    canceled: { variant: 'outline', icon: CircleSlashIcon },
    ended: { variant: 'outline', icon: CircleSlashIcon },
};

const PAYMENT_STYLES: Record<Payment['status'], BadgeStyle> = {
    paid: { variant: 'secondary', icon: CircleCheckIcon },
    pending: { variant: 'outline', icon: CircleDashedIcon },
    failed: { variant: 'destructive', icon: CircleAlertIcon },
    refunded: { variant: 'outline', icon: RotateCcwIcon },
};

function StatusBadge({ style, label }: { style: BadgeStyle; label: string }) {
    const Icon = style.icon;

    return (
        <Badge variant={style.variant}>
            <Icon data-icon="inline-start" />
            {label}
        </Badge>
    );
}

export function SubscriptionStatusBadge({
    subscription,
}: {
    subscription: Subscription;
}) {
    return (
        <StatusBadge
            style={SUBSCRIPTION_STYLES[subscription.status]}
            label={subscription.statusLabel}
        />
    );
}

export function PaymentStatusBadge({ payment }: { payment: Payment }) {
    return (
        <StatusBadge
            style={PAYMENT_STYLES[payment.status]}
            label={payment.statusLabel}
        />
    );
}

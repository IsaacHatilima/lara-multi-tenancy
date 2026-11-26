import type { ReactNode } from 'react';
import { Card, CardContent, CardHeader } from '@/components/ui/card';
import { formatDate } from '../format';
import type { Payment, Subscription } from '../types';
import { PaymentStatusBadge, SubscriptionStatusBadge } from './status-badges';

type Props = {
    subscription: Subscription | null;
    lastPayment: Payment | undefined;
    paidCount: number;
    totalPaid: string;
};

/** Plan, status, last payment and lifetime total as equal tiles. */
export function BillingTiles({
    subscription,
    lastPayment,
    paidCount,
    totalPaid,
}: Props) {
    return (
        <dl className="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <Tile
                label="Plan"
                value={subscription?.planName ?? 'None'}
                detail={subscription?.price}
            />
            <Tile
                label="Status"
                value={
                    subscription ? (
                        <SubscriptionStatusBadge subscription={subscription} />
                    ) : (
                        'No subscription'
                    )
                }
                detail={subscription ? statusDetail(subscription) : undefined}
            />
            <Tile
                label="Last payment"
                value={lastPayment?.amount ?? 'None'}
                detail={
                    lastPayment ? (
                        <span className="flex items-center gap-2">
                            {formatDate(lastPayment.attemptedAt)}
                            <PaymentStatusBadge payment={lastPayment} />
                        </span>
                    ) : undefined
                }
            />
            <Tile
                label="Total paid"
                value={totalPaid}
                detail={`${paidCount} ${paidCount === 1 ? 'payment' : 'payments'}`}
            />
        </dl>
    );
}

function statusDetail(subscription: Subscription): string {
    const periodEnd = formatDate(subscription.currentPeriodEndsAt);

    switch (subscription.status) {
        case 'active':
            return `Renews ${periodEnd}`;
        case 'trialing':
            return `Trial ends ${periodEnd}`;
        case 'past_due':
        case 'read_only':
            return 'Last charge failed';
        case 'canceled':
        case 'ended':
            return `Ends ${periodEnd}`;
    }
}

function Tile({
    label,
    value,
    detail,
}: {
    label: string;
    value: ReactNode;
    detail?: ReactNode;
}) {
    return (
        <Card size="sm">
            <CardHeader>
                <dt className="text-label font-semibold tracking-[0.06em] text-muted-foreground uppercase">
                    {label}
                </dt>
            </CardHeader>
            <CardContent className="flex flex-col gap-1">
                <dd className="text-lg font-semibold tabular-nums">{value}</dd>
                {detail && (
                    <dd className="text-sm text-muted-foreground tabular-nums">
                        {detail}
                    </dd>
                )}
            </CardContent>
        </Card>
    );
}

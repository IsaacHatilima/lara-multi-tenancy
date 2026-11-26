import { Head, Link } from '@inertiajs/react';
import { PencilIcon } from 'lucide-react';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { edit } from '@/routes/tenants';
import { BillingTiles } from './components/billing-tiles';
import { DetailList } from './components/detail-list';
import { DomainChips } from './components/domain-chips';
import { PaymentHistory } from './components/payment-history';
import { formatDate } from './format';
import type { TenantOverview } from './types';

type Props = {
    overview: TenantOverview;
};

export default function ShowTenant({ overview }: Props) {
    const { tenant, subscription, payments, compliance } = overview;
    const paidCount = payments.filter(
        (payment) => payment.status === 'paid',
    ).length;

    return (
        <>
            <Head title={tenant.name} />

            <div className="flex flex-col gap-6">
                <header className="flex flex-wrap items-start justify-between gap-4">
                    <div className="flex min-w-0 flex-col gap-1">
                        <h1 className="truncate text-xl font-semibold">
                            {tenant.name}
                        </h1>
                        <p className="text-sm text-muted-foreground">
                            Customer since{' '}
                            <time dateTime={tenant.createdAt}>
                                {formatDate(tenant.createdAt)}
                            </time>
                        </p>
                    </div>
                    <Button
                        variant="outline"
                        nativeButton={false}
                        render={<Link href={edit(tenant.id)} />}
                    >
                        <PencilIcon />
                        Edit details
                    </Button>
                </header>

                <BillingTiles
                    subscription={subscription}
                    lastPayment={payments[0]}
                    paidCount={paidCount}
                    totalPaid={overview.totalPaid}
                />

                <div className="grid items-start gap-6 lg:grid-cols-[minmax(0,3fr)_minmax(0,2fr)]">
                    <PaymentHistory payments={payments} />

                    <div className="flex flex-col gap-6">
                        {subscription && (
                            <DetailList
                                title="Subscription"
                                items={[
                                    {
                                        label: 'Plan',
                                        value: `${subscription.planName}, ${subscription.price}`,
                                    },
                                    {
                                        label: 'Started',
                                        value: formatDate(
                                            subscription.startedAt,
                                        ),
                                    },
                                    {
                                        label: 'Current period ends',
                                        value: formatDate(
                                            subscription.currentPeriodEndsAt,
                                        ),
                                    },
                                    {
                                        label: 'Payment method',
                                        value: subscription.paymentMethod,
                                    },
                                ]}
                            />
                        )}

                        <DetailList
                            title="Legal and compliance"
                            items={[
                                {
                                    label: 'Legal name',
                                    value: compliance.legalName,
                                },
                                {
                                    label: 'Registration number',
                                    value: compliance.registrationNumber,
                                },
                                {
                                    label: 'Tax number',
                                    value: compliance.taxNumber,
                                },
                                {
                                    label: 'Registered address',
                                    value:
                                        compliance.address.length > 0 ? (
                                            <address className="not-italic">
                                                {compliance.address.map(
                                                    (line) => (
                                                        <span
                                                            key={line}
                                                            className="block"
                                                        >
                                                            {line}
                                                        </span>
                                                    ),
                                                )}
                                                {compliance.countryCode && (
                                                    <span className="block">
                                                        {compliance.countryCode}
                                                    </span>
                                                )}
                                            </address>
                                        ) : null,
                                },
                                {
                                    label: 'Data region',
                                    value: compliance.dataRegion,
                                },
                                {
                                    label: `Terms (${compliance.termsVersion})`,
                                    value: compliance.termsAcceptedAt
                                        ? `Accepted ${formatDate(compliance.termsAcceptedAt)}`
                                        : 'Not accepted',
                                },
                                {
                                    label: 'Data processing agreement',
                                    value: compliance.dataProcessingAgreementSignedAt
                                        ? `Signed ${formatDate(compliance.dataProcessingAgreementSignedAt)}`
                                        : 'Not signed',
                                },
                            ]}
                        />

                        <Card>
                            <CardHeader>
                                <CardTitle>
                                    <h2>Domains</h2>
                                </CardTitle>
                            </CardHeader>
                            <CardContent>
                                <DomainChips domains={tenant.domains} />
                            </CardContent>
                        </Card>
                    </div>
                </div>
            </div>
        </>
    );
}

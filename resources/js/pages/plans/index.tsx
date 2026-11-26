import { Head, Link } from '@inertiajs/react';
import { LayersIcon, PlusIcon } from 'lucide-react';
import { Button } from '@/components/ui/button';
import {
    Empty,
    EmptyContent,
    EmptyDescription,
    EmptyHeader,
    EmptyMedia,
    EmptyTitle,
} from '@/components/ui/empty';
import {
    StackContent,
    StackedCard,
    StackHeader,
} from '@/components/ui/stacked-card';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import { EDGE_CELL_PADDING } from '@/lib/table-classes';
import { create } from '@/routes/plans';
import { PlanStatusBadge } from './components/plan-status-badge';
import type { Plan } from './types';

type Props = {
    plans: Plan[];
};

export default function PlansIndex({ plans }: Props) {
    return (
        <>
            <Head title="Plans" />
            <h1 className="sr-only">Plans</h1>

            <StackedCard top className="h-auto">
                <StackHeader className="justify-end">
                    <NewPlanButton />
                </StackHeader>

                <StackContent className="py-0">
                    {plans.length === 0 ? (
                        <PlansEmpty />
                    ) : (
                        <Table className={EDGE_CELL_PADDING}>
                            <TableHeader>
                                <TableRow>
                                    <TableHead className="sticky left-0 bg-card">
                                        Plan
                                    </TableHead>
                                    <TableHead>Company</TableHead>
                                    <TableHead>Status</TableHead>
                                    <TableHead>Interval</TableHead>
                                    <TableHead className="text-right">
                                        Price
                                    </TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                {plans.map((plan) => (
                                    <TableRow
                                        key={plan.id}
                                        className={
                                            plan.status === 'inactive'
                                                ? 'text-muted-foreground'
                                                : undefined
                                        }
                                    >
                                        <TableCell className="sticky left-0 max-w-64 bg-card">
                                            <span className="block truncate font-medium">
                                                {plan.name}
                                            </span>
                                            <span className="block truncate font-mono text-muted-foreground">
                                                {plan.code}
                                            </span>
                                        </TableCell>
                                        <TableCell className="max-w-56 truncate">
                                            {plan.tenantName}
                                        </TableCell>
                                        <TableCell>
                                            <PlanStatusBadge plan={plan} />
                                        </TableCell>
                                        <TableCell>
                                            {plan.intervalLabel}
                                        </TableCell>
                                        <TableCell className="text-right tabular-nums">
                                            {plan.price}
                                        </TableCell>
                                    </TableRow>
                                ))}
                            </TableBody>
                        </Table>
                    )}
                </StackContent>
            </StackedCard>
        </>
    );
}

function NewPlanButton() {
    return (
        <Button nativeButton={false} render={<Link href={create()} />}>
            <PlusIcon />
            New plan
        </Button>
    );
}

function PlansEmpty() {
    return (
        <Empty>
            <EmptyHeader>
                <EmptyMedia variant="icon">
                    <LayersIcon />
                </EmptyMedia>
                <EmptyTitle>No plans yet</EmptyTitle>
                <EmptyDescription>
                    A plan sets the price and features a company subscribes to.
                </EmptyDescription>
            </EmptyHeader>
            <EmptyContent>
                <NewPlanButton />
            </EmptyContent>
        </Empty>
    );
}

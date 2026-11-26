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
import { EDGE_CELL_PADDING, STICKY_FIRST_CELL } from '@/lib/table-classes';
import { cn } from '@/lib/utils';
import { create, edit } from '@/routes/plans';
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
                                    <TableHead className={STICKY_FIRST_CELL}>
                                        Plan
                                    </TableHead>
                                    <TableHead>Description</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                {plans.map((plan) => (
                                    <TableRow key={plan.id}>
                                        <TableCell
                                            className={cn(
                                                STICKY_FIRST_CELL,
                                                'max-w-64',
                                            )}
                                        >
                                            <Link
                                                href={edit(plan.id)}
                                                className="block truncate font-medium hover:underline"
                                            >
                                                {plan.name}
                                            </Link>
                                            <span className="block truncate font-mono text-muted-foreground">
                                                {plan.code}
                                            </span>
                                        </TableCell>
                                        <TableCell className="max-w-96 truncate text-muted-foreground">
                                            {plan.description}
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
                    A plan sets the features a company can use.
                </EmptyDescription>
            </EmptyHeader>
            <EmptyContent>
                <NewPlanButton />
            </EmptyContent>
        </Empty>
    );
}

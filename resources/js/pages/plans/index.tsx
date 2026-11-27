import { Head, Link } from '@inertiajs/react';
import { LayersIcon, PlusIcon } from 'lucide-react';
import { useState } from 'react';
import PlanController from '@/actions/App/Http/Controllers/Plans/PlanController';
import { ListPagination } from '@/components/list-pagination';
import { ListSearch } from '@/components/list-search';
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
    StackFooter,
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
import { edit, index } from '@/routes/plans';
import type { Paginated } from '@/types';
import { CreatePlanDialog } from './components/create-plan-dialog';
import type { BillingIntervalOption, CurrencyOption, Plan } from './types';

type Props = {
    plans: Paginated<Plan>;
    filters: { search: string | null };
    currencies: CurrencyOption[];
    intervals: BillingIntervalOption[];
};

export default function PlansIndex({
    plans,
    filters,
    currencies,
    intervals,
}: Props) {
    const [creating, setCreating] = useState(false);
    const openCreate = () => setCreating(true);

    return (
        <>
            <Head title="Plans" />
            <h1 className="sr-only">Plans</h1>

            <StackedCard top bottom={plans.data.length > 0} className="h-auto">
                <StackHeader className="justify-between">
                    <ListSearch
                        form={PlanController.index.form()}
                        label="Search plans"
                        placeholder="Search name or code"
                        search={filters.search}
                    />
                    <NewPlanButton onClick={openCreate} />
                </StackHeader>

                <StackContent className="py-0">
                    {plans.data.length === 0 ? (
                        <PlansEmpty
                            search={filters.search}
                            onCreate={openCreate}
                        />
                    ) : (
                        <Table className={EDGE_CELL_PADDING}>
                            <TableHeader>
                                <TableRow>
                                    <TableHead className={STICKY_FIRST_CELL}>
                                        Plan
                                    </TableHead>
                                    <TableHead className="text-right">
                                        Price
                                    </TableHead>
                                    <TableHead>Description</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                {plans.data.map((plan) => (
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
                                        <TableCell className="text-right tabular-nums">
                                            <span className="block">
                                                {plan.price}
                                                <span className="text-muted-foreground">
                                                    {' / '}
                                                    {plan.interval}
                                                </span>
                                            </span>
                                            {plan.trialDays > 0 && (
                                                <span className="block text-muted-foreground">
                                                    {plan.trialDays}-day trial
                                                </span>
                                            )}
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

                <StackFooter>
                    <ListPagination page={plans} noun="plans" />
                </StackFooter>
            </StackedCard>

            <CreatePlanDialog
                currencies={currencies}
                intervals={intervals}
                open={creating}
                onOpenChange={setCreating}
            />
        </>
    );
}

function NewPlanButton({ onClick }: { onClick: () => void }) {
    return (
        <Button onClick={onClick}>
            <PlusIcon />
            New plan
        </Button>
    );
}

function PlansEmpty({
    search,
    onCreate,
}: {
    search: string | null;
    onCreate: () => void;
}) {
    return (
        <Empty>
            <EmptyHeader>
                <EmptyMedia variant="icon">
                    <LayersIcon />
                </EmptyMedia>
                <EmptyTitle>
                    {search ? 'No plans match your search' : 'No plans yet'}
                </EmptyTitle>
                <EmptyDescription>
                    {search
                        ? 'Try another name or code.'
                        : 'A plan sets the features a company can use.'}
                </EmptyDescription>
            </EmptyHeader>
            <EmptyContent>
                {search ? (
                    <Button
                        nativeButton={false}
                        variant="outline"
                        render={<Link href={index()} />}
                    >
                        Clear search
                    </Button>
                ) : (
                    <NewPlanButton onClick={onCreate} />
                )}
            </EmptyContent>
        </Empty>
    );
}

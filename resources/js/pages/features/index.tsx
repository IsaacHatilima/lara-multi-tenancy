import { Head } from '@inertiajs/react';
import { PuzzleIcon } from 'lucide-react';
import { Badge } from '@/components/ui/badge';
import {
    Empty,
    EmptyDescription,
    EmptyHeader,
    EmptyMedia,
    EmptyTitle,
} from '@/components/ui/empty';
import { StackContent, StackedCard } from '@/components/ui/stacked-card';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import { EDGE_CELL_PADDING } from '@/lib/table-classes';
import type { Feature } from './types';

type Props = {
    features: Feature[];
};

export default function FeaturesIndex({ features }: Props) {
    return (
        <>
            <Head title="Features" />
            <h1 className="sr-only">Features</h1>

            <StackedCard className="h-auto">
                <StackContent className="py-0">
                    {features.length === 0 ? (
                        <FeaturesEmpty />
                    ) : (
                        <Table className={EDGE_CELL_PADDING}>
                            <TableHeader>
                                <TableRow>
                                    <TableHead className="sticky left-0 bg-card">
                                        Feature
                                    </TableHead>
                                    <TableHead>Limit unit</TableHead>
                                    <TableHead>Active plans</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                {features.map((feature) => (
                                    <TableRow key={feature.key}>
                                        <TableCell className="sticky left-0 max-w-64 bg-card">
                                            <span className="block truncate font-mono">
                                                {feature.key}
                                            </span>
                                        </TableCell>
                                        <TableCell
                                            className={
                                                feature.unitLabel === null
                                                    ? 'text-muted-foreground'
                                                    : undefined
                                            }
                                        >
                                            {feature.unitLabel ?? 'None'}
                                        </TableCell>
                                        <TableCell>
                                            <FeaturePlans feature={feature} />
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

function FeaturePlans({ feature }: { feature: Feature }) {
    if (feature.plans.length === 0) {
        return <span className="text-muted-foreground">No active plan</span>;
    }

    return (
        <ul className="flex flex-wrap gap-1">
            {feature.plans.map((plan) => (
                <li key={plan.id}>
                    <Badge variant="outline" title={plan.code}>
                        {plan.name}
                        {plan.tenantName && (
                            <span className="text-muted-foreground">
                                {plan.tenantName}
                            </span>
                        )}
                    </Badge>
                </li>
            ))}
        </ul>
    );
}

function FeaturesEmpty() {
    return (
        <Empty>
            <EmptyHeader>
                <EmptyMedia variant="icon">
                    <PuzzleIcon />
                </EmptyMedia>
                <EmptyTitle>No features yet</EmptyTitle>
                <EmptyDescription>
                    Features come from the code. Each one appears here once it
                    is built, and plans include it from their edit page.
                </EmptyDescription>
            </EmptyHeader>
        </Empty>
    );
}

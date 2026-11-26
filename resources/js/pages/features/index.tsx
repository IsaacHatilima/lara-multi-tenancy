import { Head, Link } from '@inertiajs/react';
import { PlusIcon, PuzzleIcon } from 'lucide-react';
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
import { create, edit } from '@/routes/features';
import type { Feature } from './types';

type Props = {
    features: Feature[];
};

export default function FeaturesIndex({ features }: Props) {
    return (
        <>
            <Head title="Features" />
            <h1 className="sr-only">Features</h1>

            <StackedCard top className="h-auto">
                <StackHeader className="justify-end">
                    <NewFeatureButton />
                </StackHeader>

                <StackContent className="py-0">
                    {features.length === 0 ? (
                        <FeaturesEmpty />
                    ) : (
                        <Table className={EDGE_CELL_PADDING}>
                            <TableHeader>
                                <TableRow>
                                    <TableHead className={STICKY_FIRST_CELL}>
                                        Feature
                                    </TableHead>
                                    <TableHead>Limit unit</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                {features.map((feature) => (
                                    <TableRow key={feature.id}>
                                        <TableCell
                                            className={cn(
                                                STICKY_FIRST_CELL,
                                                'max-w-64',
                                            )}
                                        >
                                            <Link
                                                href={edit(feature.id)}
                                                className="block truncate font-medium hover:underline"
                                            >
                                                {feature.name}
                                            </Link>
                                            <span className="block truncate font-mono text-muted-foreground">
                                                {feature.key}
                                            </span>
                                        </TableCell>
                                        <TableCell
                                            className={
                                                feature.limitUnitLabel === null
                                                    ? 'text-muted-foreground'
                                                    : undefined
                                            }
                                        >
                                            {feature.limitUnitLabel ?? 'None'}
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

function NewFeatureButton() {
    return (
        <Button nativeButton={false} render={<Link href={create()} />}>
            <PlusIcon />
            New feature
        </Button>
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
                    A feature is something a plan can include, such as
                    messaging. Plans include features from their edit page.
                </EmptyDescription>
            </EmptyHeader>
            <EmptyContent>
                <NewFeatureButton />
            </EmptyContent>
        </Empty>
    );
}

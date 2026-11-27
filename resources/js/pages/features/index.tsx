import { Head, Link } from '@inertiajs/react';
import { PlusIcon, PuzzleIcon } from 'lucide-react';
import { useState } from 'react';
import FeatureController from '@/actions/App/Http/Controllers/Features/FeatureController';
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
import { edit, index } from '@/routes/features';
import type { Paginated } from '@/types';
import { CreateFeatureDialog } from './components/create-feature-dialog';
import type { Feature, LimitUnitOption } from './types';

type Props = {
    features: Paginated<Feature>;
    filters: { search: string | null };
    limitUnits: LimitUnitOption[];
};

export default function FeaturesIndex({
    features,
    filters,
    limitUnits,
}: Props) {
    const [creating, setCreating] = useState(false);
    const openCreate = () => setCreating(true);

    return (
        <>
            <Head title="Features" />
            <h1 className="sr-only">Features</h1>

            <StackedCard
                top
                bottom={features.data.length > 0}
                className="h-auto"
            >
                <StackHeader className="justify-between">
                    <ListSearch
                        form={FeatureController.index.form()}
                        label="Search features"
                        placeholder="Search name or key"
                        search={filters.search}
                    />
                    <NewFeatureButton onClick={openCreate} />
                </StackHeader>

                <StackContent className="py-0">
                    {features.data.length === 0 ? (
                        <FeaturesEmpty
                            search={filters.search}
                            onCreate={openCreate}
                        />
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
                                {features.data.map((feature) => (
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

                <StackFooter>
                    <ListPagination page={features} noun="features" />
                </StackFooter>
            </StackedCard>

            <CreateFeatureDialog
                limitUnits={limitUnits}
                open={creating}
                onOpenChange={setCreating}
            />
        </>
    );
}

function NewFeatureButton({ onClick }: { onClick: () => void }) {
    return (
        <Button onClick={onClick}>
            <PlusIcon />
            New feature
        </Button>
    );
}

function FeaturesEmpty({
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
                    <PuzzleIcon />
                </EmptyMedia>
                <EmptyTitle>
                    {search
                        ? 'No features match your search'
                        : 'No features yet'}
                </EmptyTitle>
                <EmptyDescription>
                    {search
                        ? 'Try another name or key.'
                        : 'A feature is something a plan can include, such as messaging. Plans include features from their edit page.'}
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
                    <NewFeatureButton onClick={onCreate} />
                )}
            </EmptyContent>
        </Empty>
    );
}

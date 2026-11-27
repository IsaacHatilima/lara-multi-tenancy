import { Form, Head, Link } from '@inertiajs/react';
import { Building2Icon, PlusIcon, SearchIcon } from 'lucide-react';
import TenantController from '@/actions/App/Http/Controllers/Tenants/TenantController';
import { Button } from '@/components/ui/button';
import {
    Empty,
    EmptyContent,
    EmptyDescription,
    EmptyHeader,
    EmptyMedia,
    EmptyTitle,
} from '@/components/ui/empty';
import { Input } from '@/components/ui/input';
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
import { create, index, show } from '@/routes/tenants';
import { DomainChips } from './components/domain-chips';
import { formatDate } from './format';
import { TenantPagination } from './components/tenant-pagination';
import type { Paginated, Tenant } from './types';

type Props = {
    tenants: Paginated<Tenant>;
    filters: { search: string | null };
};

export default function TenantsIndex({ tenants, filters }: Props) {
    return (
        <>
            <Head title="Tenants" />
            <h1 className="sr-only">Tenants</h1>

            <StackedCard
                top
                bottom={tenants.data.length > 0}
                className="h-auto"
            >
                <StackHeader className="justify-between">
                    <Form
                        {...TenantController.index.form()}
                        className="relative w-full max-w-72"
                        role="search"
                    >
                        <SearchIcon
                            aria-hidden
                            className="pointer-events-none absolute top-1/2 left-2.5 size-3.5 -translate-y-1/2 text-muted-foreground"
                        />
                        <Input
                            type="search"
                            name="filter[search]"
                            aria-label="Search tenants"
                            placeholder="Search name, email or domain"
                            defaultValue={filters.search ?? ''}
                            className="pl-8"
                        />
                    </Form>

                    <Button
                        nativeButton={false}
                        render={<Link href={create()} />}
                    >
                        <PlusIcon />
                        New tenant
                    </Button>
                </StackHeader>

                <StackContent className="py-0">
                    {tenants.data.length === 0 ? (
                        <TenantsEmpty search={filters.search} />
                    ) : (
                        <Table className={EDGE_CELL_PADDING}>
                            <TableHeader>
                                <TableRow>
                                    <TableHead className={STICKY_FIRST_CELL}>
                                        Company
                                    </TableHead>
                                    <TableHead>Domains</TableHead>
                                    <TableHead className="text-right">
                                        Created
                                    </TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                {tenants.data.map((tenant) => (
                                    <TableRow key={tenant.id}>
                                        <TableCell
                                            className={cn(
                                                STICKY_FIRST_CELL,
                                                'max-w-64 align-top',
                                            )}
                                        >
                                            <Link
                                                href={show(tenant.id)}
                                                className="block truncate font-medium hover:underline"
                                            >
                                                {tenant.name}
                                            </Link>
                                            {tenant.email && (
                                                <span
                                                    className="block truncate text-muted-foreground"
                                                    title={tenant.email}
                                                >
                                                    {tenant.email}
                                                </span>
                                            )}
                                        </TableCell>
                                        <TableCell className="max-w-80 align-top">
                                            <DomainChips
                                                domains={tenant.domains}
                                            />
                                        </TableCell>
                                        <TableCell className="text-right align-top text-muted-foreground tabular-nums">
                                            <time dateTime={tenant.createdAt}>
                                                {formatDate(tenant.createdAt)}
                                            </time>
                                        </TableCell>
                                    </TableRow>
                                ))}
                            </TableBody>
                        </Table>
                    )}
                </StackContent>

                <StackFooter>
                    <TenantPagination page={tenants} noun="tenants" />
                </StackFooter>
            </StackedCard>
        </>
    );
}

function TenantsEmpty({ search }: { search: string | null }) {
    return (
        <Empty>
            <EmptyHeader>
                <EmptyMedia variant="icon">
                    <Building2Icon />
                </EmptyMedia>
                <EmptyTitle>
                    {search ? 'No tenants match your search' : 'No tenants yet'}
                </EmptyTitle>
                <EmptyDescription>
                    {search
                        ? 'Try another name, email or domain.'
                        : 'Each company you add gets its own database and domain.'}
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
                    <Button
                        nativeButton={false}
                        render={<Link href={create()} />}
                    >
                        <PlusIcon />
                        New tenant
                    </Button>
                )}
            </EmptyContent>
        </Empty>
    );
}

import { Link } from '@inertiajs/react';
import { ChevronLeftIcon, ChevronRightIcon } from 'lucide-react';
import type { ReactNode } from 'react';
import { Button } from '@/components/ui/button';
import type { Paginated } from '../types';

type Props<T> = {
    page: Paginated<T>;
    noun: string;
};

/** The visible range, with previous and next links once there is more than one page. */
export function TenantPagination<T>({ page, noun }: Props<T>) {
    return (
        <nav
            aria-label="Pagination"
            className="flex w-full items-center justify-between gap-4"
        >
            <p className="tabular-nums">
                {page.from}–{page.to} of {page.total} {noun}
            </p>
            {page.last_page > 1 && (
                <div className="flex gap-2">
                    <PageLink url={page.prev_page_url} label="Previous">
                        <ChevronLeftIcon />
                        Previous
                    </PageLink>
                    <PageLink url={page.next_page_url} label="Next">
                        Next
                        <ChevronRightIcon />
                    </PageLink>
                </div>
            )}
        </nav>
    );
}

type PageLinkProps = {
    url: string | null;
    label: string;
    children: ReactNode;
};

function PageLink({ url, label, children }: PageLinkProps) {
    return (
        <Button
            variant="outline"
            size="sm"
            aria-label={`${label} page`}
            disabled={url === null}
            nativeButton={url === null}
            render={url ? <Link href={url} preserveScroll /> : undefined}
        >
            {children}
        </Button>
    );
}

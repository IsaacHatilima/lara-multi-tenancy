import { useEffect, useState } from 'react';
import type { ReactNode } from 'react';
import { usePathname } from '@/components/shells/shared/active-navigation';
import { cn } from '@/lib/utils';

/** Set once the first page has rendered, so the initial load never fades. */
let firstPageShown = false;

function PageFrame({ children }: { children: ReactNode }) {
    // Read once per page: each new path mounts a new frame.
    const [fade] = useState(() => firstPageShown);

    useEffect(() => {
        firstPageShown = true;
    }, []);

    return (
        <div
            className={cn(
                'flex min-w-0 flex-1 flex-col gap-4',
                fade && 'animate-page-enter',
            )}
        >
            {children}
        </div>
    );
}

/**
 * Fades the page content in when the user moves to a different path. The frame
 * is keyed on the path, so a save or reload that returns to the same path keeps
 * it mounted and nothing flashes. The shell stays outside it.
 */
export function PageEnter({ children }: { children: ReactNode }) {
    const pathname = usePathname();

    return <PageFrame key={pathname}>{children}</PageFrame>;
}

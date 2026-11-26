import { usePage } from '@inertiajs/react';

import { useNavigation } from '@/hooks/use-navigation';
import type { NavItem, NavSection } from '@/lib/navigation';

export interface ActiveNavigation {
    section: NavSection | null;
    item: NavItem | null;
}

function isWithin(pathname: string, url: string): boolean {
    return pathname === url || pathname.startsWith(`${url}/`);
}

/**
 * Finds the nav item for `pathname`: an exact match first, otherwise the
 * deepest item the path sits under (so /projects/42 lights up "All projects").
 */
export function findActiveNavigation(
    pathname: string,
    sections: NavSection[],
): ActiveNavigation {
    const path = pathname.length > 1 ? pathname.replace(/\/+$/, '') : pathname;
    const candidates = sections.flatMap((section) =>
        section.items.map((item) => ({ section, item })),
    );

    const exact = candidates.find(({ item }) => item.url === path);
    if (exact) {
        return exact;
    }

    const nested = candidates
        .filter(({ item }) => isWithin(path, item.url))
        .sort((a, b) => b.item.url.length - a.item.url.length)[0];

    return nested ?? { section: null, item: null };
}

/** The current path, without the query string, from Inertia's page URL. */
export function usePathname(): string {
    const { url } = usePage();
    return url.split(/[?#]/)[0] || '/';
}

export function useActiveNavigation(): ActiveNavigation {
    const { all } = useNavigation();

    return findActiveNavigation(usePathname(), all);
}

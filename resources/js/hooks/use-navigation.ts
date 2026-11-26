import { usePage } from '@inertiajs/react';
import {
    mainNavigationFor,
    settingsNavigation,
    type NavSection,
} from '@/lib/navigation';

export type Navigation = {
    /** The rail's sections. */
    main: NavSection[];
    /** Every page, for breadcrumbs, the active state and app search. */
    all: NavSection[];
};

/** Navigation for the current visitor: tenant and plan management appear only when the server allows them. */
export function useNavigation(): Navigation {
    const { canManageTenants, canManagePlans } = usePage().props;
    const main = mainNavigationFor({ canManageTenants, canManagePlans });

    return { main, all: [...main, settingsNavigation] };
}

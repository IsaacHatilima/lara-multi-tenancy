import {
    BoxesIcon,
    Building2Icon,
    FingerprintIcon,
    KeyRoundIcon,
    LayersIcon,
    LayoutDashboardIcon,
    Settings2Icon,
    ShieldCheckIcon,
    SlidersHorizontalIcon,
    UserRoundIcon,
} from 'lucide-react';
import type { LucideIcon } from 'lucide-react';
import { dashboard } from '@/routes';
import { edit as editAppearance } from '@/routes/appearance';
import { edit as editProfile } from '@/routes/profile';
import { index as tenantsIndex } from '@/routes/tenants';
import { edit as editPasskeys } from '@/routes/passkeys';
import { index as plansIndex } from '@/routes/plans';
import { edit as editTwoFactor } from '@/routes/two-factor';
import { edit as editPassword } from '@/routes/user-password';

export type NavItem = {
    title: string;
    url: string;
    icon?: LucideIcon;
};

export type NavGroup = {
    title: string;
    items: NavItem[];
};

export type NavSection = {
    title: string;
    /** Where the section's breadcrumb links to, usually its first page. */
    url: string;
    icon: LucideIcon;
    items: NavItem[];
    groups?: NavGroup[];
    /** RailShell draws a divider above this section. */
    dividerBefore?: boolean;
};

/**
 * Every shell renders from this one list, so breadcrumbs, the active state and
 * app search all follow it. Add a page here when you add its route.
 */
export const mainNavigation: NavSection[] = [
    {
        title: 'Dashboard',
        url: dashboard().url,
        icon: LayoutDashboardIcon,
        items: [
            {
                title: 'Overview',
                url: dashboard().url,
                icon: LayoutDashboardIcon,
            },
        ],
    },
];

/**
 * Shown only on the central host of a managed deployment, when the server
 * shares `canManageTenants`; use `useNavigation()` to get it in context.
 */
export const tenantsNavigation: NavSection = {
    title: 'Tenants',
    url: tenantsIndex().url,
    icon: Building2Icon,
    items: [
        {
            title: 'All tenants',
            url: tenantsIndex().url,
            icon: Building2Icon,
        },
    ],
};

/**
 * The platform owner's catalogue. Shown only on the central host of a managed
 * deployment to staff holding `plans.manage`, when the server shares
 * `canManagePlans`.
 */
export const platformNavigation: NavSection = {
    title: 'Platform',
    url: plansIndex().url,
    icon: BoxesIcon,
    items: [
        {
            title: 'Plans',
            url: plansIndex().url,
            icon: LayersIcon,
        },
    ],
};

export const settingsNavigationGroups: NavGroup[] = [
    {
        title: 'Personal',
        items: [
            { title: 'Profile', url: editProfile().url, icon: UserRoundIcon },
            {
                title: 'Appearance',
                url: editAppearance().url,
                icon: SlidersHorizontalIcon,
            },
        ],
    },
    {
        title: 'Security',
        items: [
            { title: 'Password', url: editPassword().url, icon: KeyRoundIcon },
            {
                title: 'Two-factor authentication',
                url: editTwoFactor().url,
                icon: ShieldCheckIcon,
            },
            {
                title: 'Passkeys',
                url: editPasskeys().url,
                icon: FingerprintIcon,
            },
        ],
    },
];

/** Entered through the account menu rather than the rail, but still searchable. */
export const settingsNavigation: NavSection = {
    title: 'Settings',
    url: editProfile().url,
    icon: Settings2Icon,
    groups: settingsNavigationGroups,
    items: settingsNavigationGroups.flatMap((group) => group.items),
};

type NavigationAccess = {
    canManageTenants: boolean;
    canManagePlans: boolean;
};

/** The rail's sections for the current visitor. */
export function mainNavigationFor({
    canManageTenants,
    canManagePlans,
}: NavigationAccess): NavSection[] {
    return [
        ...mainNavigation,
        ...(canManageTenants ? [tenantsNavigation] : []),
        ...(canManagePlans ? [platformNavigation] : []),
    ];
}

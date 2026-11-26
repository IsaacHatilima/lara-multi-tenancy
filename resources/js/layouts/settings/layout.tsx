import { useContext } from 'react';
import type { PropsWithChildren } from 'react';
import { ShellSectionNavigationContext } from '@/components/shells/shared/section-navigation-context';
import { SettingsNavigation } from './settings-navigation';

/**
 * RailShell already lists the settings pages in its panel, so settings only
 * draws its own navigation under a shell that doesn't.
 */
export default function SettingsLayout({ children }: PropsWithChildren) {
    const shellHasSectionNavigation = useContext(ShellSectionNavigationContext);

    if (shellHasSectionNavigation) {
        return (
            <div className="mx-auto flex w-full max-w-160 flex-col gap-6">
                {children}
            </div>
        );
    }

    return (
        <div className="flex min-w-0 flex-1 flex-col gap-4 lg:flex-row">
            <SettingsNavigation />
            <div className="mx-auto flex w-full max-w-160 min-w-0 flex-1 flex-col gap-6">
                {children}
            </div>
        </div>
    );
}

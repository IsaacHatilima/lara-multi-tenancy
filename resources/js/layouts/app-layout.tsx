import type { ReactNode } from 'react';
import { PageEnter } from '@/components/page-enter';
import { ReadOnlyNotice } from '@/components/read-only-notice';
// Pick the dashboard frame here. Pages and navigation work with every shell.
import { RailShell as DashboardShell } from '@/components/shells';

export default function AppLayout({ children }: { children: ReactNode }) {
    return (
        <DashboardShell>
            <ReadOnlyNotice />
            <PageEnter>{children}</PageEnter>
        </DashboardShell>
    );
}

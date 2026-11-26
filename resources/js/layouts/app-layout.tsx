import type { ReactNode } from 'react';
import { PageEnter } from '@/components/page-enter';
// Pick the dashboard frame here. Pages and navigation work with every shell.
import { RailShell as DashboardShell } from '@/components/shells';

export default function AppLayout({ children }: { children: ReactNode }) {
    return (
        <DashboardShell>
            <PageEnter>{children}</PageEnter>
        </DashboardShell>
    );
}

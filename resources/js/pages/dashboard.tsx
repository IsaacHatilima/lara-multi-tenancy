import { Head, usePage } from '@inertiajs/react';
import { DashboardOverview } from './dashboard/components/dashboard-overview';
import { DrawerExamples } from './dashboard/components/drawer-examples';
import { SheetExample } from './dashboard/components/sheet-example';

export default function Dashboard() {
    const { user } = usePage().props.auth;

    return (
        <>
            <Head title="Overview" />
            <DashboardOverview userName={user.name} />
            <DrawerExamples />
            <SheetExample />
        </>
    );
}

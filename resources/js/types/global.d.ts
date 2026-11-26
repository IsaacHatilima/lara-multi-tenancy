import type { InertiaUIFlashData } from '@inertiaui/notifications-react';
import type { Auth } from '@/types/auth';

declare module 'react' {
    interface InputHTMLAttributes<T> {
        passwordrules?: string;
    }
}

declare module '@inertiajs/core' {
    export interface InertiaConfig {
        flashDataType: InertiaUIFlashData;
        sharedPageProps: {
            name: string;
            tenantId: string | null;
            canManageTenants: boolean;
            canManagePlans: boolean;
            auth: Auth;
            sidebarOpen: boolean;
            [key: string]: unknown;
        };
    }
}

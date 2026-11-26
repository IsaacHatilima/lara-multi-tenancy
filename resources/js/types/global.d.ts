import type { Auth } from '@/types/auth';
import type { FeatureSet } from '@/types/features';

declare module 'react' {
    interface InputHTMLAttributes<T> {
        passwordrules?: string;
    }
}

declare module '@inertiajs/core' {
    export interface InertiaConfig {
        sharedPageProps: {
            name: string;
            tenantId: string | null;
            canManageTenants: boolean;
            auth: Auth;
            sidebarOpen: boolean;
            features?: FeatureSet;
            [key: string]: unknown;
        };
    }
}

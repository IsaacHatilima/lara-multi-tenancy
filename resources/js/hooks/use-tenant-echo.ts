import { usePage } from '@inertiajs/react';
import { useEcho } from '@laravel/echo-react';
import type { DependencyList } from 'react';

export function useTenantEcho<TPayload = unknown>(
    channelName: string,
    event?: string | string[],
    callback?: (payload: TPayload) => void,
    dependencies?: DependencyList,
) {
    const { tenantId } = usePage().props;

    return useEcho<TPayload>(
        tenantId ? `${tenantId}.${channelName}` : channelName,
        event,
        callback,
        dependencies,
    );
}

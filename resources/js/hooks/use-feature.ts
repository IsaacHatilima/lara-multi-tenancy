import { usePage } from '@inertiajs/react';
import type { FeatureItem } from '@/types';

/**
 * The feature as the host's plan grants it. A key the payload doesn't list is
 * enabled only on an ungated host; guests, who get no payload, have nothing.
 */
export function useFeature(key: string): FeatureItem {
    const { features } = usePage().props;

    return (
        features?.items[key] ?? {
            enabled: features?.gated === false,
            limit: null,
            unit: null,
            availableOn: [],
        }
    );
}

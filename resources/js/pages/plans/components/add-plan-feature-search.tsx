import { router, useHttp } from '@inertiajs/react';
import { PlusIcon, SearchIcon } from 'lucide-react';
import { useEffect, useState } from 'react';
import PlanFeatureController from '@/actions/App/Http/Controllers/Plans/PlanFeatureController';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import type { Feature } from '../../features/types';

type Props = {
    planId: string;
};

/** Searches the features the plan doesn't include yet; each match can be added at once, unlimited. */
export function AddPlanFeatureSearch({ planId }: Props) {
    const [search, setSearch] = useState('');
    const [addingId, setAddingId] = useState<number | null>(null);
    const [error, setError] = useState<string | undefined>();
    const [searchFailed, setSearchFailed] = useState(false);
    const { get, cancel, response, processing } = useHttp<
        Record<string, never>,
        Feature[]
    >();
    const term = search.trim();

    useEffect(() => {
        if (term === '') {
            return;
        }

        let cancelled = false;
        const timer = setTimeout(() => {
            setSearchFailed(false);
            get(
                PlanFeatureController.search.url(planId, {
                    query: { search: term },
                }),
                { onCancel: () => (cancelled = true) },
            ).catch(() => {
                if (!cancelled) {
                    setSearchFailed(true);
                }
            });
        }, 200);

        return () => {
            clearTimeout(timer);
            cancel();
        };
    }, [term, planId, get, cancel]);

    function add(feature: Feature) {
        setAddingId(feature.id);
        router.post(
            PlanFeatureController.store.url(planId),
            { feature_id: feature.id },
            {
                preserveScroll: true,
                onSuccess: () => {
                    setSearch('');
                    setError(undefined);
                },
                onError: (errors) => setError(errors.feature_id),
                onFinish: () => setAddingId(null),
            },
        );
    }

    const matches = term === '' ? [] : (response ?? []);

    return (
        <div className="flex flex-col gap-2">
            <div className="relative">
                <SearchIcon
                    aria-hidden
                    className="pointer-events-none absolute top-1/2 left-2.5 size-3.5 -translate-y-1/2 text-muted-foreground"
                />
                <Input
                    type="search"
                    value={search}
                    onChange={(event) => setSearch(event.target.value)}
                    aria-label="Search features to add"
                    placeholder="Search features to add"
                    autoComplete="off"
                    className="pl-8"
                />
            </div>
            <InputError message={error} />

            {term !== '' && (
                <ul
                    aria-label="Matching features"
                    aria-busy={processing}
                    className="flex flex-col divide-y rounded-lg border"
                >
                    {matches.length === 0 ? (
                        <li className="px-3 py-2.5 text-muted-foreground">
                            {searchFailed
                                ? 'The search failed. Try again.'
                                : processing
                                  ? 'Searching…'
                                  : 'No feature left to add matches this search.'}
                        </li>
                    ) : (
                        matches.map((feature) => (
                            <li
                                key={feature.id}
                                className="flex items-center gap-3 px-3 py-2"
                            >
                                <span className="flex min-w-0 flex-1 flex-col">
                                    <span className="truncate">
                                        {feature.name}
                                    </span>
                                    <span className="truncate font-mono text-xs text-muted-foreground">
                                        {feature.key}
                                    </span>
                                </span>
                                <Button
                                    variant="outline"
                                    size="sm"
                                    disabled={addingId !== null}
                                    onClick={() => add(feature)}
                                    aria-label={`Add ${feature.name}`}
                                >
                                    <PlusIcon />
                                    Add
                                </Button>
                            </li>
                        ))
                    )}
                </ul>
            )}
        </div>
    );
}

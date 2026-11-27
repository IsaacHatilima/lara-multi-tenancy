import { Form, router } from '@inertiajs/react';
import { SearchIcon } from 'lucide-react';
import { Input } from '@/components/ui/input';
import type { RouteFormDefinition } from '@/wayfinder';

type Props = {
    form: RouteFormDefinition<'get'>;
    label: string;
    placeholder: string;
    search: string | null;
};

/**
 * A list's search box; it submits `filter[search]` to the list's own route. Emptying it, with the clear button or
 * by deleting the text, goes back to the unfiltered list.
 */
export function ListSearch({ form, label, placeholder, search }: Props) {
    return (
        <Form {...form} className="relative w-full max-w-72" role="search">
            <SearchIcon
                aria-hidden
                className="pointer-events-none absolute top-1/2 left-2.5 size-3.5 -translate-y-1/2 text-muted-foreground"
            />
            <Input
                type="search"
                name="filter[search]"
                aria-label={label}
                placeholder={placeholder}
                defaultValue={search ?? ''}
                onChange={(event) => {
                    if (event.target.value === '' && search !== null) {
                        router.get(form.action, {}, { preserveScroll: true });
                    }
                }}
                className="pl-8"
            />
        </Form>
    );
}

import { Form } from '@inertiajs/react';
import { SearchIcon } from 'lucide-react';
import { Input } from '@/components/ui/input';
import type { RouteFormDefinition } from '@/wayfinder';

type Props = {
    form: RouteFormDefinition<'get'>;
    label: string;
    placeholder: string;
    search: string | null;
};

/** A list's search box; it submits `filter[search]` to the list's own route. */
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
                className="pl-8"
            />
        </Form>
    );
}

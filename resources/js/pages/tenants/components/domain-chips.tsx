import { Badge } from '@/components/ui/badge';
import type { Domain } from '../types';

/** A company's domains, each with the slot it occupies. */
export function DomainChips({ domains }: { domains: Domain[] }) {
    if (domains.length === 0) {
        return <span className="text-muted-foreground">No domain</span>;
    }

    return (
        <ul className="flex flex-col gap-1">
            {domains.map((domain) => (
                <li key={domain.id} className="flex min-w-0 items-center gap-2">
                    <span
                        className="truncate font-mono text-xs"
                        title={domain.domain}
                    >
                        {domain.domain}
                    </span>
                    <Badge
                        variant={
                            domain.slot === 'primary' ? 'secondary' : 'outline'
                        }
                    >
                        {domain.slotLabel}
                    </Badge>
                </li>
            ))}
        </ul>
    );
}

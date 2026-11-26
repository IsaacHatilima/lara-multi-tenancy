import type { ReactNode } from 'react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';

export type DetailItem = {
    label: string;
    value: ReactNode;
};

/** A card of label and value pairs; empty values read "Not provided". */
export function DetailList({
    title,
    items,
}: {
    title: string;
    items: DetailItem[];
}) {
    return (
        <Card>
            <CardHeader>
                <CardTitle>
                    <h2>{title}</h2>
                </CardTitle>
            </CardHeader>
            <CardContent className="px-0">
                <dl className="divide-y border-t">
                    {items.map((item) => (
                        <div
                            key={item.label}
                            className="grid grid-cols-[minmax(0,2fr)_minmax(0,3fr)] gap-3 px-(--card-spacing) py-2 text-sm"
                        >
                            <dt className="text-muted-foreground">
                                {item.label}
                            </dt>
                            <dd className="min-w-0 break-words">
                                {item.value ?? (
                                    <span className="text-muted-foreground">
                                        Not provided
                                    </span>
                                )}
                            </dd>
                        </div>
                    ))}
                </dl>
            </CardContent>
        </Card>
    );
}

import type { ReactNode } from 'react';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';

type Props = {
    title: string;
    description?: string;
    children: ReactNode;
};

/** One settings section: a borderless card with its title, description and content. */
export function SettingsSection({ title, description, children }: Props) {
    return (
        <Card border={false}>
            <CardHeader>
                <CardTitle>
                    <h2>{title}</h2>
                </CardTitle>
                {description && (
                    <CardDescription>{description}</CardDescription>
                )}
            </CardHeader>
            <CardContent className="flex flex-col gap-6">
                {children}
            </CardContent>
        </Card>
    );
}

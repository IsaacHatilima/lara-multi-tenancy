import { Form } from '@inertiajs/react';
import { useRef } from 'react';
import { Button } from '@/components/ui/button';
import {
    ResponsiveDialog,
    ResponsiveDialogClose,
    ResponsiveDialogContent,
    ResponsiveDialogDescription,
    ResponsiveDialogFooter,
    ResponsiveDialogHeader,
    ResponsiveDialogTitle,
    ResponsiveDialogTrigger,
} from '@/components/ui/responsive-dialog';
import type { RouteFormDefinition } from '@/wayfinder';

type Props = {
    form: RouteFormDefinition<'post'>;
    title: string;
    description: string;
    confirmLabel: string;
};

/** A delete button that asks first; Cancel takes focus so Enter can't delete by accident. */
export function ConfirmDeleteDialog({
    form,
    title,
    description,
    confirmLabel,
}: Props) {
    const cancelRef = useRef<HTMLButtonElement>(null);

    return (
        <ResponsiveDialog>
            <ResponsiveDialogTrigger
                render={<Button variant="destructive" type="button" />}
            >
                {confirmLabel}
            </ResponsiveDialogTrigger>
            <ResponsiveDialogContent
                className="sm:max-w-md"
                initialFocus={cancelRef}
            >
                <Form {...form} className="contents">
                    {({ processing }) => (
                        <>
                            <ResponsiveDialogHeader>
                                <ResponsiveDialogTitle>
                                    {title}
                                </ResponsiveDialogTitle>
                                <ResponsiveDialogDescription>
                                    {description}
                                </ResponsiveDialogDescription>
                            </ResponsiveDialogHeader>
                            <ResponsiveDialogFooter>
                                <ResponsiveDialogClose
                                    render={
                                        <Button
                                            ref={cancelRef}
                                            variant="outline"
                                            type="button"
                                        />
                                    }
                                >
                                    Cancel
                                </ResponsiveDialogClose>
                                <Button
                                    type="submit"
                                    variant="destructive"
                                    disabled={processing}
                                >
                                    {confirmLabel}
                                </Button>
                            </ResponsiveDialogFooter>
                        </>
                    )}
                </Form>
            </ResponsiveDialogContent>
        </ResponsiveDialog>
    );
}

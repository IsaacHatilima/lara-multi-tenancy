import { Form } from '@inertiajs/react';
import { useRef } from 'react';
import PlanFeatureController from '@/actions/App/Http/Controllers/Plans/PlanFeatureController';
import { Button } from '@/components/ui/button';
import {
    ResponsiveDialog,
    ResponsiveDialogClose,
    ResponsiveDialogContent,
    ResponsiveDialogDescription,
    ResponsiveDialogFooter,
    ResponsiveDialogHeader,
    ResponsiveDialogTitle,
} from '@/components/ui/responsive-dialog';
import type { PlanFeature } from '../types';

type Props = {
    planId: string;
    planName: string;
    feature: PlanFeature;
    open: boolean;
    onOpenChange: (open: boolean) => void;
};

/** Confirms taking a feature off the plan; Cancel takes focus so Enter can't remove it by accident. */
export function RemovePlanFeatureDialog({
    planId,
    planName,
    feature,
    open,
    onOpenChange,
}: Props) {
    const cancelRef = useRef<HTMLButtonElement>(null);

    return (
        <ResponsiveDialog open={open} onOpenChange={onOpenChange}>
            <ResponsiveDialogContent
                className="sm:max-w-md"
                initialFocus={cancelRef}
            >
                <Form
                    {...PlanFeatureController.destroy.form([
                        planId,
                        feature.id,
                    ])}
                    options={{ preserveScroll: true }}
                    onSuccess={() => onOpenChange(false)}
                    className="contents"
                >
                    {({ processing }) => (
                        <>
                            <ResponsiveDialogHeader>
                                <ResponsiveDialogTitle>
                                    Remove {feature.name}?
                                </ResponsiveDialogTitle>
                                <ResponsiveDialogDescription>
                                    Companies on {planName} lose this feature.
                                    You can add it again later, with a new
                                    limit.
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
                                    Remove feature
                                </Button>
                            </ResponsiveDialogFooter>
                        </>
                    )}
                </Form>
            </ResponsiveDialogContent>
        </ResponsiveDialog>
    );
}

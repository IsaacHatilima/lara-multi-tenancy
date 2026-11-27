import { Form } from '@inertiajs/react';
import { useState } from 'react';
import PlanController from '@/actions/App/Http/Controllers/Plans/PlanController';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Label } from '@/components/ui/label';
import {
    ResponsiveDialog,
    ResponsiveDialogBody,
    ResponsiveDialogClose,
    ResponsiveDialogContent,
    ResponsiveDialogDescription,
    ResponsiveDialogFooter,
    ResponsiveDialogHeader,
    ResponsiveDialogTitle,
} from '@/components/ui/responsive-dialog';
import type { BillingIntervalOption, CurrencyOption } from '../types';
import { PlanFields } from './plan-fields';
import { PlanPricingFields } from './plan-pricing-fields';

type Props = {
    currencies: CurrencyOption[];
    intervals: BillingIntervalOption[];
    open: boolean;
    onOpenChange: (open: boolean) => void;
};

/**
 * Creates a plan, then opens it to choose its features. With "Create more" ticked it stays on the list with an
 * empty form for the next one.
 */
export function CreatePlanDialog({
    currencies,
    intervals,
    open,
    onOpenChange,
}: Props) {
    const [createMore, setCreateMore] = useState(false);
    const [created, setCreated] = useState(0);

    return (
        <ResponsiveDialog
            open={open}
            onOpenChange={onOpenChange}
            disablePointerDismissal
        >
            <ResponsiveDialogContent className="sm:max-w-xl">
                <Form
                    {...PlanController.store.form()}
                    options={{ preserveScroll: true }}
                    onSuccess={() =>
                        createMore && setCreated((count) => count + 1)
                    }
                    className="contents"
                >
                    {({ processing, errors }) => (
                        <>
                            <ResponsiveDialogHeader>
                                <ResponsiveDialogTitle>
                                    New plan
                                </ResponsiveDialogTitle>
                                <ResponsiveDialogDescription>
                                    Each plan has its own code. Choose its
                                    features after creating it.
                                </ResponsiveDialogDescription>
                            </ResponsiveDialogHeader>
                            <ResponsiveDialogBody
                                key={created}
                                className="flex flex-col gap-4"
                            >
                                <PlanFields
                                    defaults={{
                                        name: '',
                                        code: '',
                                        description: null,
                                    }}
                                    errors={errors}
                                />
                                <PlanPricingFields
                                    defaults={{
                                        priceMinor: 0,
                                        currency: currencies[0].value,
                                        interval: intervals[0].value,
                                        trialDays: 0,
                                    }}
                                    currencies={currencies}
                                    intervals={intervals}
                                    errors={errors}
                                />
                            </ResponsiveDialogBody>
                            <ResponsiveDialogFooter className="sm:items-center">
                                <Label className="mr-auto font-normal">
                                    <Checkbox
                                        checked={createMore}
                                        onCheckedChange={setCreateMore}
                                    />
                                    Create more
                                </Label>
                                {createMore && (
                                    <input
                                        type="hidden"
                                        name="create_more"
                                        value="1"
                                    />
                                )}
                                <ResponsiveDialogClose
                                    render={
                                        <Button
                                            variant="outline"
                                            type="button"
                                        />
                                    }
                                >
                                    Cancel
                                </ResponsiveDialogClose>
                                <Button type="submit" disabled={processing}>
                                    Create plan
                                </Button>
                            </ResponsiveDialogFooter>
                        </>
                    )}
                </Form>
            </ResponsiveDialogContent>
        </ResponsiveDialog>
    );
}

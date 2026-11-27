import { Form } from '@inertiajs/react';
import { useState } from 'react';
import FeatureController from '@/actions/App/Http/Controllers/Features/FeatureController';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
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
import type { LimitUnitOption } from '../types';
import { FeatureFields } from './feature-fields';

type Props = {
    limitUnits: LimitUnitOption[];
    open: boolean;
    onOpenChange: (open: boolean) => void;
};

/** Creates a feature. With "Create more" ticked it stays open on an empty form for the next one. */
export function CreateFeatureDialog({ limitUnits, open, onOpenChange }: Props) {
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
                    {...FeatureController.store.form()}
                    options={{ preserveScroll: true }}
                    onSuccess={() =>
                        createMore
                            ? setCreated((count) => count + 1)
                            : onOpenChange(false)
                    }
                    className="contents"
                >
                    {({ processing, errors }) => (
                        <>
                            <ResponsiveDialogHeader>
                                <ResponsiveDialogTitle>
                                    New feature
                                </ResponsiveDialogTitle>
                                <ResponsiveDialogDescription>
                                    Code checks the feature by its key, so the
                                    key can't be changed later. A limit unit
                                    lets each plan cap the feature.
                                </ResponsiveDialogDescription>
                            </ResponsiveDialogHeader>
                            <ResponsiveDialogBody
                                key={created}
                                className="flex flex-col gap-4"
                            >
                                <div className="grid gap-2">
                                    <Label htmlFor="key">Key</Label>
                                    <Input
                                        id="key"
                                        name="key"
                                        required
                                        autoFocus
                                        autoComplete="off"
                                        spellCheck={false}
                                        placeholder="messaging"
                                        className="font-mono"
                                    />
                                    <InputError message={errors.key} />
                                </div>
                                <FeatureFields
                                    defaults={{
                                        name: '',
                                        description: null,
                                        limitUnit: null,
                                    }}
                                    limitUnits={limitUnits}
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
                                    Create feature
                                </Button>
                            </ResponsiveDialogFooter>
                        </>
                    )}
                </Form>
            </ResponsiveDialogContent>
        </ResponsiveDialog>
    );
}

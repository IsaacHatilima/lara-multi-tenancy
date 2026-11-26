import { Form } from '@inertiajs/react';
import TenantDomainController from '@/actions/App/Http/Controllers/Tenants/TenantDomainController';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
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
import type { Domain } from '../types';

type Props = {
    tenantId: string;
    domain: Domain;
    open: boolean;
    onOpenChange: (open: boolean) => void;
};

/** Renames one domain; it keeps its slot. */
export function EditDomainDialog({
    tenantId,
    domain,
    open,
    onOpenChange,
}: Props) {
    return (
        <ResponsiveDialog
            open={open}
            onOpenChange={onOpenChange}
            disablePointerDismissal
        >
            <ResponsiveDialogContent className="sm:max-w-md">
                <Form
                    {...TenantDomainController.update.form([
                        tenantId,
                        domain.id,
                    ])}
                    options={{ preserveScroll: true }}
                    onSuccess={() => onOpenChange(false)}
                    className="contents"
                >
                    {({ processing, errors }) => (
                        <>
                            <ResponsiveDialogHeader>
                                <ResponsiveDialogTitle>
                                    Edit {domain.slotLabel.toLowerCase()} domain
                                </ResponsiveDialogTitle>
                                <ResponsiveDialogDescription>
                                    Visitors on the old hostname stop reaching
                                    this company once you save.
                                </ResponsiveDialogDescription>
                            </ResponsiveDialogHeader>
                            <ResponsiveDialogBody>
                                <div className="grid gap-2">
                                    <Label htmlFor={`domain-${domain.id}`}>
                                        Hostname
                                    </Label>
                                    <Input
                                        id={`domain-${domain.id}`}
                                        name="domain"
                                        defaultValue={domain.domain}
                                        required
                                        autoComplete="off"
                                        spellCheck={false}
                                        className="font-mono"
                                    />
                                    <InputError
                                        message={errors.domain ?? errors.slot}
                                    />
                                </div>
                            </ResponsiveDialogBody>
                            <ResponsiveDialogFooter>
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
                                    Save domain
                                </Button>
                            </ResponsiveDialogFooter>
                        </>
                    )}
                </Form>
            </ResponsiveDialogContent>
        </ResponsiveDialog>
    );
}

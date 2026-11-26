import { Form } from '@inertiajs/react';
import { useRef } from 'react';
import TenantDomainController from '@/actions/App/Http/Controllers/Tenants/TenantDomainController';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
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
    companyName: string;
    domain: Domain;
    open: boolean;
    onOpenChange: (open: boolean) => void;
};

/** Confirms removing a domain; Cancel takes focus so Enter can't remove it by accident. */
export function RemoveDomainDialog({
    tenantId,
    companyName,
    domain,
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
                    {...TenantDomainController.destroy.form([
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
                                    Remove {domain.domain}?
                                </ResponsiveDialogTitle>
                                <ResponsiveDialogDescription>
                                    {companyName} will no longer be reachable on
                                    this hostname. You can add it again later.
                                </ResponsiveDialogDescription>
                            </ResponsiveDialogHeader>
                            {errors.domain && (
                                <ResponsiveDialogBody>
                                    <InputError message={errors.domain} />
                                </ResponsiveDialogBody>
                            )}
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
                                    Remove domain
                                </Button>
                            </ResponsiveDialogFooter>
                        </>
                    )}
                </Form>
            </ResponsiveDialogContent>
        </ResponsiveDialog>
    );
}

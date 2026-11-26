import { Form } from '@inertiajs/react';
import { PlusIcon } from 'lucide-react';
import TenantDomainController from '@/actions/App/Http/Controllers/Tenants/TenantDomainController';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

/** Adds a domain in the company's free slot. */
export function AddDomainForm({ tenantId }: { tenantId: string }) {
    return (
        <Form
            {...TenantDomainController.store.form(tenantId)}
            options={{ preserveScroll: true }}
            resetOnSuccess
            className="grid gap-2"
        >
            {({ processing, errors }) => (
                <>
                    <Label htmlFor="new-domain">Add a domain</Label>
                    <div className="flex gap-2">
                        <Input
                            id="new-domain"
                            name="domain"
                            required
                            autoComplete="off"
                            spellCheck={false}
                            placeholder="portal.example.com"
                            className="font-mono"
                        />
                        <Button
                            type="submit"
                            variant="outline"
                            disabled={processing}
                        >
                            <PlusIcon />
                            Add domain
                        </Button>
                    </div>
                    <InputError message={errors.domain ?? errors.slot} />
                </>
            )}
        </Form>
    );
}

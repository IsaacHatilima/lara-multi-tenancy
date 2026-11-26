import { Form, Head, Link } from '@inertiajs/react';
import TenantController from '@/actions/App/Http/Controllers/Tenants/TenantController';
import InputError from '@/components/input-error';
import { SettingsSection } from '@/components/settings-section';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { index } from '@/routes/tenants';
import { CompanyFields } from './components/company-fields';

export default function CreateTenant() {
    return (
        <>
            <Head title="New tenant" />
            <h1 className="sr-only">New tenant</h1>

            <Form
                {...TenantController.store.form()}
                className="mx-auto flex w-full max-w-160 flex-col gap-6"
            >
                {({ processing, errors }) => (
                    <>
                        <SettingsSection
                            title="Primary domain"
                            description="The hostname the company signs in on. Creating the tenant also creates its database."
                        >
                            <div className="grid gap-2">
                                <Label htmlFor="domain">Hostname</Label>
                                <Input
                                    id="domain"
                                    name="domain"
                                    required
                                    autoFocus
                                    autoComplete="off"
                                    spellCheck={false}
                                    placeholder="acme.example.com"
                                    className="font-mono"
                                />
                                <InputError message={errors.domain} />
                            </div>
                        </SettingsSection>

                        <SettingsSection
                            title="Company details"
                            description="Only the name is required; the rest can be added later."
                        >
                            <CompanyFields errors={errors} />
                        </SettingsSection>

                        <div className="flex justify-end gap-2">
                            <Button
                                nativeButton={false}
                                variant="outline"
                                render={<Link href={index()} />}
                            >
                                Cancel
                            </Button>
                            <Button type="submit" disabled={processing}>
                                Create tenant
                            </Button>
                        </div>
                    </>
                )}
            </Form>
        </>
    );
}

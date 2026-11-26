import { Form, Head } from '@inertiajs/react';
import TenantController from '@/actions/App/Http/Controllers/Tenants/TenantController';
import { SettingsSection } from '@/components/settings-section';
import { Button } from '@/components/ui/button';
import { CompanyFields } from './components/company-fields';
import { TenantDomains } from './components/tenant-domains';
import type { Tenant } from './types';

type Props = {
    tenant: Tenant;
};

export default function EditTenant({ tenant }: Props) {
    const primaryDomain = tenant.domains[0]?.domain;

    return (
        <>
            <Head title={tenant.name} />

            <div className="mx-auto flex w-full max-w-160 flex-col gap-6">
                <header className="flex flex-col gap-1">
                    <h1 className="text-xl font-semibold">{tenant.name}</h1>
                    {primaryDomain && (
                        <a
                            href={`https://${primaryDomain}`}
                            target="_blank"
                            rel="noreferrer"
                            className="w-fit font-mono text-xs text-muted-foreground hover:text-foreground hover:underline"
                        >
                            {primaryDomain}
                        </a>
                    )}
                </header>

                <TenantDomains tenant={tenant} />

                <SettingsSection
                    title="Company details"
                    description="The company's contact, registration and address details."
                >
                    <Form
                        {...TenantController.update.form(tenant.id)}
                        options={{ preserveScroll: true }}
                        className="flex flex-col gap-6"
                    >
                        {({ processing, errors }) => (
                            <>
                                <CompanyFields
                                    tenant={tenant}
                                    errors={errors}
                                />
                                <div className="flex justify-end">
                                    <Button type="submit" disabled={processing}>
                                        Save details
                                    </Button>
                                </div>
                            </>
                        )}
                    </Form>
                </SettingsSection>
            </div>
        </>
    );
}

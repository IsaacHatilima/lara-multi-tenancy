import { Head } from '@inertiajs/react';
import { SettingsSection } from '@/components/settings-section';
import type { Props } from '@/components/manage-passkeys';
import ManagePasskeys from '@/components/manage-passkeys';

export default function Passkeys(props: Props) {
    return (
        <>
            <Head title="Passkeys" />

            <h1 className="sr-only">Passkeys</h1>

            {props.canManagePasskeys ? (
                <ManagePasskeys {...props} />
            ) : (
                <SettingsSection
                    title="Passkeys"
                    description="Manage your passkeys for passwordless sign-in"
                >
                    <p className="text-sm text-muted-foreground">
                        Passkeys are not enabled for this application.
                    </p>
                </SettingsSection>
            )}
        </>
    );
}

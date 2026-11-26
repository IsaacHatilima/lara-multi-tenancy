import { Head } from '@inertiajs/react';
import { SettingsSection } from '@/components/settings-section';
import type { Props } from '@/components/manage-two-factor';
import ManageTwoFactor from '@/components/manage-two-factor';

export default function TwoFactor(props: Props) {
    return (
        <>
            <Head title="Two-factor authentication" />

            <h1 className="sr-only">Two-factor authentication</h1>

            {props.canManageTwoFactor ? (
                <ManageTwoFactor {...props} />
            ) : (
                <SettingsSection
                    title="Two-factor authentication"
                    description="Manage your two-factor authentication settings"
                >
                    <p className="text-sm text-muted-foreground">
                        Two-factor authentication is not enabled for this
                        application.
                    </p>
                </SettingsSection>
            )}
        </>
    );
}

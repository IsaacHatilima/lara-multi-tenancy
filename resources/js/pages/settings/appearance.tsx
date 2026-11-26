import { Head } from '@inertiajs/react';
import { AppearancePicker } from '@/components/appearance-picker';
import { SettingsSection } from '@/components/settings-section';

export default function Appearance() {
    return (
        <>
            <Head title="Appearance settings" />

            <h1 className="sr-only">Appearance settings</h1>

            <SettingsSection
                title="Theme"
                description="Choose how the app looks. System follows your device setting."
            >
                <AppearancePicker />
            </SettingsSection>
        </>
    );
}

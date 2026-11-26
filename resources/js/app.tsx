import { createInertiaApp } from '@inertiajs/react';
import { Toaster, initNotifications } from '@inertiaui/notifications-react';
import { configureEcho } from '@laravel/echo-react';
import { MotionProvider } from '@/components/motion-provider';
import { TooltipProvider } from '@/components/ui/tooltip';
import { initializeTheme, useAppearanceShortcut } from '@/hooks/use-appearance';
import AppLayout from '@/layouts/app-layout';
import AuthLayout from '@/layouts/auth-layout';
import SettingsLayout from '@/layouts/settings/layout';

configureEcho({
    broadcaster: 'reverb',
});

// Shows the toasts the server flashes with Toast::...->flash().
initNotifications({ duration: 5000, limit: 1 });

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

function AppearanceShortcut() {
    useAppearanceShortcut();

    return null;
}

void createInertiaApp({
    title: (title) => (title ? `${title} - ${appName}` : appName),
    layout: (name) => {
        switch (true) {
            case name === 'welcome':
                return null;
            case name.startsWith('auth/'):
                return AuthLayout;
            case name.startsWith('settings/'):
                return [AppLayout, SettingsLayout];
            default:
                return AppLayout;
        }
    },
    strictMode: true,
    withApp(app) {
        return (
            <MotionProvider>
                <TooltipProvider>
                    {app}
                    <Toaster position="bottom-center" />
                    <AppearanceShortcut />
                </TooltipProvider>
            </MotionProvider>
        );
    },
    progress: {
        color: '#4B5563',
    },
});

// This will set light / dark mode on load...
initializeTheme();

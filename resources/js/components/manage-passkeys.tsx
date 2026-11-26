import { router } from '@inertiajs/react';
import { KeyRound } from 'lucide-react';
import { AnimatePresence, m } from 'motion/react';
import { destroy } from '@/actions/Laravel/Passkeys/Http/Controllers/PasskeyRegistrationController';
import { SettingsSection } from '@/components/settings-section';
import PasskeyItem from '@/components/passkey-item';
import PasskeyRegistration from '@/components/passkey-register';
import type { Passkey } from '@/types/auth';
import { resize } from '@/lib/motion';

export type Props = {
    canManagePasskeys?: boolean;
    passkeys?: Passkey[];
};

const EmptyState = () => {
    return (
        <div className="p-8 text-center">
            <div className="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-2xl bg-muted">
                <KeyRound className="h-7 w-7 text-muted-foreground" />
            </div>
            <p className="font-medium">No passkeys yet</p>
            <p className="mt-1 text-sm text-muted-foreground">
                Add a passkey to sign in without a password
            </p>
        </div>
    );
};

export default function ManagePasskeys(props: Props) {
    const passkeys = props.passkeys ?? [];

    const handleDelete = (id: number, onError: () => void) => {
        router.delete(destroy.url(id), {
            preserveScroll: true,
            onError,
        });
    };

    const handleRegisterSuccess = () => {
        router.reload();
    };

    if (!(props.canManagePasskeys ?? false)) {
        return null;
    }

    return (
        <SettingsSection
            title="Passkeys"
            description="Manage your passkeys for passwordless sign-in"
        >
            <div className="overflow-hidden rounded-lg border border-border">
                {passkeys.length > 0 ? (
                    <AnimatePresence initial={false}>
                        {passkeys.map((passkey) => (
                            <m.div
                                key={passkey.id}
                                initial={{ height: 0, opacity: 0 }}
                                animate={{ height: 'auto', opacity: 1 }}
                                exit={{ height: 0, opacity: 0 }}
                                transition={resize}
                                className="overflow-hidden border-b last:border-b-0"
                            >
                                <PasskeyItem
                                    passkey={passkey}
                                    onDelete={handleDelete}
                                />
                            </m.div>
                        ))}
                    </AnimatePresence>
                ) : (
                    <EmptyState />
                )}
            </div>

            <PasskeyRegistration onSuccess={handleRegisterSuccess} />
        </SettingsSection>
    );
}

import { usePage } from '@inertiajs/react';
import { LockIcon } from 'lucide-react';
import { Alert, AlertTitle } from '@/components/ui/alert';

export function ReadOnlyNotice() {
    const { features } = usePage().props;

    if (!features?.readOnly) {
        return null;
    }

    return (
        <Alert>
            <LockIcon />
            <AlertTitle>
                This account is read-only because its payment is overdue. You
                can still view and delete existing data.
            </AlertTitle>
        </Alert>
    );
}

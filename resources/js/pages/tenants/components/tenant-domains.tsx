import { PencilIcon, Trash2Icon } from 'lucide-react';
import { useState } from 'react';
import type { ReactNode } from 'react';
import { SettingsSection } from '@/components/settings-section';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Tooltip,
    TooltipContent,
    TooltipTrigger,
} from '@/components/ui/tooltip';
import { MAX_DOMAINS, type Domain, type Tenant } from '../types';
import { AddDomainForm } from './add-domain-form';
import { EditDomainDialog } from './edit-domain-dialog';
import { RemoveDomainDialog } from './remove-domain-dialog';

type OpenDialog = { kind: 'edit' | 'remove'; domain: Domain } | null;

export function TenantDomains({ tenant }: { tenant: Tenant }) {
    const [dialog, setDialog] = useState<OpenDialog>(null);
    const isLastDomain = tenant.domains.length <= 1;
    const closeDialog = (open: boolean) => {
        if (!open) {
            setDialog(null);
        }
    };

    return (
        <SettingsSection
            title="Domains"
            description={`A company can be reached on up to ${MAX_DOMAINS} hostnames: a primary and a secondary.`}
        >
            <ul className="divide-y rounded-lg ring-1 ring-border">
                {tenant.domains.map((domain) => (
                    <li
                        key={domain.id}
                        className="flex items-center gap-3 px-3 py-2"
                    >
                        <span
                            className="min-w-0 flex-1 truncate font-mono text-xs"
                            title={domain.domain}
                        >
                            {domain.domain}
                        </span>
                        <Badge
                            variant={
                                domain.slot === 'primary'
                                    ? 'secondary'
                                    : 'outline'
                            }
                        >
                            {domain.slotLabel}
                        </Badge>
                        <DomainAction
                            label={`Edit ${domain.domain}`}
                            onClick={() => setDialog({ kind: 'edit', domain })}
                        >
                            <PencilIcon />
                        </DomainAction>
                        <DomainAction
                            label={
                                isLastDomain
                                    ? 'A company must keep at least one domain'
                                    : `Remove ${domain.domain}`
                            }
                            disabled={isLastDomain}
                            onClick={() =>
                                setDialog({ kind: 'remove', domain })
                            }
                        >
                            <Trash2Icon />
                        </DomainAction>
                    </li>
                ))}
            </ul>

            {tenant.domains.length < MAX_DOMAINS && (
                <AddDomainForm tenantId={tenant.id} />
            )}

            {dialog?.kind === 'edit' && (
                <EditDomainDialog
                    tenantId={tenant.id}
                    domain={dialog.domain}
                    open
                    onOpenChange={closeDialog}
                />
            )}
            {dialog?.kind === 'remove' && (
                <RemoveDomainDialog
                    tenantId={tenant.id}
                    companyName={tenant.name}
                    domain={dialog.domain}
                    open
                    onOpenChange={closeDialog}
                />
            )}
        </SettingsSection>
    );
}

type DomainActionProps = {
    label: string;
    disabled?: boolean;
    onClick: () => void;
    children: ReactNode;
};

function DomainAction({
    label,
    disabled = false,
    onClick,
    children,
}: DomainActionProps) {
    return (
        <Tooltip>
            <TooltipTrigger
                render={
                    <Button
                        variant="ghost"
                        size="icon-sm"
                        aria-label={label}
                        aria-disabled={disabled}
                        onClick={disabled ? undefined : onClick}
                        className={disabled ? 'opacity-50' : undefined}
                    />
                }
            >
                {children}
            </TooltipTrigger>
            <TooltipContent>{label}</TooltipContent>
        </Tooltip>
    );
}

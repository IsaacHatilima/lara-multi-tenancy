import {
    ChevronsUpDownIcon,
    LogOutIcon,
    MonitorIcon,
    MoonIcon,
    Settings2Icon,
    SunIcon,
    type LucideIcon,
} from 'lucide-react';
import { Link, router, usePage } from '@inertiajs/react';

import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuGroup,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuRadioGroup,
    DropdownMenuRadioItem,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import {
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
    useSidebar,
} from '@/components/ui/sidebar';
import {
    Tooltip,
    TooltipContent,
    TooltipTrigger,
} from '@/components/ui/tooltip';
import { useAppearance, type Appearance } from '@/hooks/use-appearance';
import { settingsNavigation } from '@/lib/navigation';
import { logout } from '@/routes';
import type { User } from '@/types';

const themeOptions = [
    { value: 'light', label: 'Light', icon: SunIcon },
    { value: 'dark', label: 'Dark', icon: MoonIcon },
    { value: 'system', label: 'System', icon: MonitorIcon },
] as const satisfies ReadonlyArray<{
    value: Appearance;
    label: string;
    icon: LucideIcon;
}>;

function isAppearance(value: unknown): value is Appearance {
    return themeOptions.some((option) => option.value === value);
}

export function initials(name: string): string {
    const letters = name
        .trim()
        .split(/\s+/)
        .slice(0, 2)
        .map((word) => word.charAt(0).toUpperCase())
        .join('');

    return letters || '?';
}

function UserIdentity({ user }: { user: User }) {
    return (
        <>
            <Avatar className="rounded-lg">
                {user.avatar && (
                    <AvatarImage
                        src={user.avatar}
                        alt={user.profile.full_name}
                    />
                )}
                <AvatarFallback className="rounded-lg">
                    {initials(user.profile.full_name)}
                </AvatarFallback>
            </Avatar>
            <div className="grid flex-1 text-left text-sm leading-tight">
                <span className="truncate font-medium">
                    {user.profile.full_name}
                </span>
                <span className="truncate text-xs">{user.email}</span>
            </div>
        </>
    );
}

interface ShellUserMenuProps {
    // RailShell's icon rail only has room for the avatar: a round one in an
    // icon button, as ChatGPT's rail has.
    compact?: boolean;
    onSettingsNavigate?: () => void;
}

function RailAvatar({ user }: { user: User }) {
    return (
        <Avatar size="sm">
            {user.avatar && <AvatarImage src={user.avatar} alt="" />}
            <AvatarFallback>{initials(user.profile.full_name)}</AvatarFallback>
        </Avatar>
    );
}

export function ShellUserMenu({
    compact = false,
    onSettingsNavigate,
}: ShellUserMenuProps) {
    const { isMobile } = useSidebar();
    const { appearance, updateAppearance } = useAppearance();
    const { user } = usePage().props.auth;

    const menu = (
        <DropdownMenu>
            <DropdownMenuTrigger
                data-account-menu-trigger=""
                render={
                    compact ? (
                        <Button
                            variant="ghost"
                            size="icon-lg"
                            aria-label={`Account menu for ${user.profile.full_name}`}
                        />
                    ) : (
                        <SidebarMenuButton
                            size="lg"
                            className="aria-expanded:bg-muted"
                        />
                    )
                }
            >
                {compact ? (
                    <RailAvatar user={user} />
                ) : (
                    <>
                        <UserIdentity user={user} />
                        <ChevronsUpDownIcon className="ml-auto size-4" />
                    </>
                )}
            </DropdownMenuTrigger>
            <DropdownMenuContent
                className="min-w-56 rounded-lg"
                side={isMobile ? 'bottom' : 'right'}
                align="end"
                sideOffset={4}
            >
                <DropdownMenuGroup>
                    <DropdownMenuLabel className="p-0 font-normal">
                        <div className="flex items-center gap-2 px-1 py-1.5 text-left text-sm">
                            <UserIdentity user={user} />
                        </div>
                    </DropdownMenuLabel>
                </DropdownMenuGroup>
                <DropdownMenuSeparator />
                <DropdownMenuGroup>
                    <DropdownMenuItem
                        render={
                            <Link
                                href={settingsNavigation.url}
                                onClick={onSettingsNavigate}
                            />
                        }
                    >
                        <Settings2Icon />
                        Settings
                    </DropdownMenuItem>
                </DropdownMenuGroup>
                <DropdownMenuSeparator />
                <DropdownMenuGroup>
                    <DropdownMenuRadioGroup
                        aria-label="Theme"
                        value={appearance}
                        onValueChange={(value) => {
                            if (isAppearance(value)) {
                                updateAppearance(value);
                            }
                        }}
                        className="mx-1 my-1 flex gap-1 rounded-lg bg-muted p-1"
                    >
                        {themeOptions.map(({ value, label, icon: Icon }) => (
                            <Tooltip key={value}>
                                <TooltipTrigger
                                    render={
                                        <DropdownMenuRadioItem
                                            value={value}
                                            aria-label={label}
                                            label={label}
                                            className="h-(--control-height) flex-1 justify-center px-0 focus-visible:ring-2 focus-visible:ring-ring max-md:h-10 data-checked:bg-popover data-checked:shadow-xs [&>[data-slot=dropdown-menu-radio-item-indicator]]:hidden"
                                        />
                                    }
                                >
                                    <Icon />
                                </TooltipTrigger>
                                <TooltipContent>{label}</TooltipContent>
                            </Tooltip>
                        ))}
                    </DropdownMenuRadioGroup>
                </DropdownMenuGroup>
                <DropdownMenuSeparator />
                <DropdownMenuGroup>
                    <DropdownMenuItem
                        render={
                            <Link
                                href={logout()}
                                as="button"
                                className="w-full"
                                onClick={() => router.flushAll()}
                                data-test="logout-button"
                            />
                        }
                    >
                        <LogOutIcon />
                        Log out
                    </DropdownMenuItem>
                </DropdownMenuGroup>
            </DropdownMenuContent>
        </DropdownMenu>
    );

    if (compact) {
        return menu;
    }
    return (
        <SidebarMenu>
            <SidebarMenuItem>{menu}</SidebarMenuItem>
        </SidebarMenu>
    );
}

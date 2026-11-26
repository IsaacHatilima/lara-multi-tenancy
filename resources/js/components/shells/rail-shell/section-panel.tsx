import { cn } from '@/lib/utils';
import { Link } from '@inertiajs/react';
import { m } from 'motion/react';
import { useId } from 'react';

import {
    SidebarContent,
    SidebarGroup,
    SidebarGroupContent,
    SidebarGroupLabel,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import type { NavSection } from '@/lib/navigation';
import { slideSpring } from '@/lib/motion';
import { useActiveNavigation } from '../shared/active-navigation';
import { ICON_STROKE_WIDTH } from './icon-stroke';

interface SectionPanelProps {
    section: NavSection;
    className?: string;
    onNavigate?: () => void;
}

// A section's pages: the links that navigate. Rendered docked beside the rail,
// or as the floating card a collapsed rail shows.
export function SectionPanel({
    section,
    className,
    onNavigate,
}: SectionPanelProps) {
    const active = useActiveNavigation();
    // Scopes the sliding highlight to this panel, so the docked panel and the
    // floating card never trade it.
    const highlightId = useId();
    const groups = section.groups ?? [{ title: null, items: section.items }];

    return (
        <div
            className={cn(
                'flex min-h-0 min-w-0 flex-col text-sidebar-foreground',
                className,
            )}
        >
            <SidebarHeader className="h-(--app-header-height) flex-row items-center py-0 pr-2.5 pl-4">
                <h2 className="truncate text-base font-semibold">
                    {section.title}
                </h2>
            </SidebarHeader>
            <SidebarContent>
                <nav
                    aria-label={`${section.title} pages`}
                    className="flex flex-col gap-3.5"
                >
                    {groups.map((group) => (
                        <SidebarGroup
                            key={group.title ?? section.title}
                            className="px-2 py-0"
                        >
                            {group.title && (
                                <SidebarGroupLabel render={<h3 />}>
                                    {group.title}
                                </SidebarGroupLabel>
                            )}
                            <SidebarGroupContent>
                                <SidebarMenu>
                                    {group.items.map((item) => {
                                        const itemActive =
                                            active.item?.url === item.url;
                                        return (
                                            <SidebarMenuItem key={item.url}>
                                                {itemActive && (
                                                    <m.span
                                                        layoutId={`${highlightId}-active-item`}
                                                        transition={slideSpring}
                                                        aria-hidden
                                                        className="absolute inset-0 rounded-md bg-sidebar-accent"
                                                    />
                                                )}
                                                <SidebarMenuButton
                                                    isActive={itemActive}
                                                    className={cn(
                                                        'relative data-active:bg-transparent data-active:hover:bg-transparent',
                                                        section.groups &&
                                                            '[&_svg]:size-3.5',
                                                    )}
                                                    render={
                                                        <Link
                                                            href={item.url}
                                                            aria-current={
                                                                itemActive
                                                                    ? 'page'
                                                                    : undefined
                                                            }
                                                            onClick={onNavigate}
                                                        />
                                                    }
                                                >
                                                    {item.icon && (
                                                        <item.icon
                                                            strokeWidth={
                                                                ICON_STROKE_WIDTH
                                                            }
                                                            className="text-sidebar-muted-foreground"
                                                        />
                                                    )}
                                                    <span>{item.title}</span>
                                                </SidebarMenuButton>
                                            </SidebarMenuItem>
                                        );
                                    })}
                                </SidebarMenu>
                            </SidebarGroupContent>
                        </SidebarGroup>
                    ))}
                </nav>
            </SidebarContent>
        </div>
    );
}

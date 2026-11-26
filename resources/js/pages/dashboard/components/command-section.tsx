import { useState } from 'react';
import { router } from '@inertiajs/react';

import { Button } from '@/components/ui/button';
import {
    Command,
    CommandDialog,
    CommandEmpty,
    CommandGroup,
    CommandInput,
    CommandItem,
    CommandList,
} from '@/components/ui/command';
import { mainNavigation } from '@/lib/navigation';

function CommandPreview({ label, stack }: { label: string; stack: boolean }) {
    const [open, setOpen] = useState(false);

    return (
        <>
            <Button variant="outline" onClick={() => setOpen(true)}>
                {label}
            </Button>
            <CommandDialog
                open={open}
                onOpenChange={setOpen}
                stack={stack}
                title="Workspace commands"
                description="Find a page in your workspace."
            >
                <Command label="Workspace commands">
                    <CommandInput
                        aria-label="Search workspace commands"
                        placeholder="Type a command or search…"
                    />
                    <div className="shrink-0 px-3 py-2">
                        <code className="block text-xs leading-relaxed wrap-anywhere whitespace-pre-wrap text-muted-foreground">
                            {`<CommandDialog${stack ? ' stack' : ''}>
  <Command label="Workspace commands">
    <CommandInput placeholder="Type a command or search…" />
    <CommandList>…</CommandList>
  </Command>
</CommandDialog>`}
                        </code>
                    </div>
                    <CommandList>
                        <CommandEmpty>No pages found.</CommandEmpty>
                        {mainNavigation.map((section) => (
                            <CommandGroup
                                key={section.title}
                                heading={section.title}
                            >
                                {section.items.map((item) => {
                                    const Icon = item.icon ?? section.icon;

                                    return (
                                        <CommandItem
                                            key={item.url}
                                            value={item.title}
                                            keywords={[section.title]}
                                            onSelect={() => {
                                                setOpen(false);
                                                router.visit(item.url);
                                            }}
                                        >
                                            <Icon />
                                            <span>{item.title}</span>
                                        </CommandItem>
                                    );
                                })}
                            </CommandGroup>
                        ))}
                    </CommandList>
                </Command>
            </CommandDialog>
        </>
    );
}

export function CommandSection() {
    return (
        <section
            aria-labelledby="commands-title"
            className="flex flex-col gap-4"
        >
            <h2 id="commands-title" className="text-sm font-medium">
                Commands
            </h2>
            <div className="flex flex-wrap gap-3">
                {[
                    { label: 'Default command', stack: false },
                    { label: 'Stacked command', stack: true },
                ].map((mode) => (
                    <CommandPreview key={mode.label} {...mode} />
                ))}
            </div>
        </section>
    );
}

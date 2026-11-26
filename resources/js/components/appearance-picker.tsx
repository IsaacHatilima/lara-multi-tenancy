import { CheckIcon } from 'lucide-react';
import { m } from 'motion/react';
import type { CSSProperties } from 'react';
import type { Appearance } from '@/hooks/use-appearance';
import { useAppearance } from '@/hooks/use-appearance';
import { slideSpring } from '@/lib/motion';
import { cn } from '@/lib/utils';

type Palette = {
    background: string;
    sidebar: string;
    surface: string;
    line: string;
    accent: string;
};

/**
 * The previews always show their own theme, whatever theme is active, so they
 * carry the light and dark tokens from app.css as fixed values.
 */
const palettes: Record<'light' | 'dark', Palette> = {
    light: {
        background: '#fcfcfd',
        sidebar: '#f3f3f2',
        surface: '#ececee',
        line: 'rgb(0 0 0 / 8%)',
        accent: '#0285f7',
    },
    dark: {
        background: '#0b0b0a',
        sidebar: '#171717',
        surface: '#232322',
        line: 'rgb(255 255 255 / 8%)',
        accent: '#ededed',
    },
};

const options: { value: Appearance; label: string }[] = [
    { value: 'system', label: 'System' },
    { value: 'light', label: 'Light' },
    { value: 'dark', label: 'Dark' },
];

function paletteStyle(palette: Palette): CSSProperties {
    return {
        '--preview-background': palette.background,
        '--preview-sidebar': palette.sidebar,
        '--preview-surface': palette.surface,
        '--preview-line': palette.line,
        '--preview-accent': palette.accent,
    } as CSSProperties;
}

/** A miniature app window: title bar, sidebar, toolbar and a grid of cards. */
function WindowPreview({
    palette,
    className,
}: {
    palette: Palette;
    className?: string;
}) {
    return (
        <div
            aria-hidden
            style={paletteStyle(palette)}
            className={cn(
                'absolute inset-0 flex flex-col bg-(--preview-background) p-2.5',
                className,
            )}
        >
            <div className="flex flex-1 flex-col overflow-hidden rounded-md border border-(--preview-line) bg-(--preview-background)">
                <div className="flex h-3.5 items-center gap-1 border-b border-(--preview-line) px-1.5">
                    <span className="size-1.5 rounded-full bg-[#ff5f57]" />
                    <span className="size-1.5 rounded-full bg-[#febc2e]" />
                    <span className="size-1.5 rounded-full bg-[#28c840]" />
                    <span className="ml-2 h-1 w-1/4 rounded-full bg-(--preview-surface)" />
                </div>
                <div className="flex flex-1">
                    <div className="flex w-1/5 flex-col gap-1 border-r border-(--preview-line) bg-(--preview-sidebar) p-1.5">
                        <span className="h-1 rounded-full bg-(--preview-surface)" />
                        <span className="h-1 rounded-full bg-(--preview-surface)" />
                        <span className="h-1 w-3/4 rounded-full bg-(--preview-surface)" />
                    </div>
                    <div className="flex flex-1 flex-col gap-1.5 p-1.5">
                        <div className="flex items-center justify-between">
                            <span className="h-1 w-1/3 rounded-full bg-(--preview-surface)" />
                            <span className="h-1.5 w-1/6 rounded-sm bg-(--preview-accent)" />
                        </div>
                        <div className="grid flex-1 grid-cols-3 gap-1">
                            {Array.from({ length: 6 }, (_, index) => (
                                <span
                                    key={index}
                                    className="rounded-sm bg-(--preview-surface)"
                                />
                            ))}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    );
}

function ThemePreview({ value }: { value: Appearance }) {
    if (value === 'system') {
        return (
            <>
                <WindowPreview palette={palettes.light} />
                <WindowPreview
                    palette={palettes.dark}
                    className="[clip-path:inset(0_0_0_50%)]"
                />
            </>
        );
    }

    return <WindowPreview palette={palettes[value]} />;
}

/** Theme choice as preview tiles; the choice is stored by useAppearance. */
export function AppearancePicker() {
    const { appearance, updateAppearance } = useAppearance();

    return (
        <fieldset className="grid grid-cols-1 gap-4 sm:grid-cols-3">
            <legend className="sr-only">Theme</legend>
            {options.map(({ value, label }) => {
                const checked = appearance === value;

                return (
                    <label
                        key={value}
                        className="group flex cursor-pointer flex-col gap-2.5"
                    >
                        <input
                            type="radio"
                            name="appearance"
                            value={value}
                            checked={checked}
                            onChange={() => updateAppearance(value)}
                            className="peer sr-only"
                        />
                        <span className="relative rounded-xl peer-focus-visible:ring-2 peer-focus-visible:ring-ring peer-focus-visible:ring-offset-2 peer-focus-visible:ring-offset-background">
                            <span className="relative block aspect-[16/10] overflow-hidden rounded-xl ring-1 ring-border transition-shadow duration-(--motion-fast) group-hover:ring-foreground/20">
                                <ThemePreview value={value} />
                            </span>
                            {checked && (
                                <m.span
                                    layoutId="appearance-selection"
                                    transition={slideSpring}
                                    aria-hidden
                                    className="pointer-events-none absolute -inset-1 rounded-[calc(var(--radius-xl)+4px)] ring-2 ring-ring"
                                >
                                    <span className="absolute bottom-3 left-3 flex size-5 items-center justify-center rounded-full bg-ring text-white shadow-sm">
                                        <CheckIcon className="size-3" />
                                    </span>
                                </m.span>
                            )}
                        </span>
                        <span
                            className={cn(
                                'text-sm',
                                'transition-colors duration-(--motion-fast)',
                                checked
                                    ? 'font-medium text-foreground'
                                    : 'text-muted-foreground group-hover:text-foreground',
                            )}
                        >
                            {label}
                        </span>
                    </label>
                );
            })}
        </fieldset>
    );
}

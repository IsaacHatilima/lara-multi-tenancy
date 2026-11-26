import { m } from 'motion/react';
import { useEffect, useRef, useState } from 'react';
import type { ReactNode } from 'react';
import { resize } from '@/lib/motion';
import { cn } from '@/lib/utils';

type Props = {
    children: ReactNode;
    className?: string;
};

/**
 * Animates to the height of its content whenever the content changes size,
 * such as a dialog moving to its next step. A 4px inner gutter keeps focus
 * rings from being clipped while the height is animating.
 */
export function AnimatedHeight({ children, className }: Props) {
    const contentRef = useRef<HTMLDivElement>(null);
    const [height, setHeight] = useState<number | 'auto'>('auto');

    useEffect(() => {
        const content = contentRef.current;

        if (!content) {
            return;
        }

        const observer = new ResizeObserver(([entry]) => {
            setHeight(entry.borderBoxSize[0].blockSize);
        });
        observer.observe(content);

        return () => observer.disconnect();
    }, []);

    return (
        <m.div
            initial={false}
            animate={{ height }}
            transition={resize}
            className={cn('-m-1 overflow-hidden', className)}
        >
            <div ref={contentRef} className="p-1">
                {children}
            </div>
        </m.div>
    );
}

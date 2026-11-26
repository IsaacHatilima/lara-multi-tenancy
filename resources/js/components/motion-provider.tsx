import { LazyMotion, MotionConfig } from 'motion/react';
import type { ReactNode } from 'react';

const loadFeatures = () =>
    import('@/lib/motion-features').then((module) => module.default);

/**
 * The app's one motion switch for the motion library. reducedMotion="user"
 * follows the device setting, so transforms and layout animations stop when it
 * asks for less motion; app.css does the same for CSS. LazyMotion with `m`
 * components loads the animation features after first paint, outside the main
 * bundle.
 */
export function MotionProvider({ children }: { children: ReactNode }) {
    return (
        <LazyMotion features={loadFeatures} strict>
            <MotionConfig reducedMotion="user">{children}</MotionConfig>
        </LazyMotion>
    );
}

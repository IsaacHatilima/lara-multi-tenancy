import type { Transition } from 'motion/react';

/**
 * Motion timing for the motion library. The same values live in app.css as
 * --motion-* custom properties for CSS; change both together.
 */
export const duration = {
    fast: 0.12,
    base: 0.2,
    slow: 0.3,
} as const;

export const easeOut = [0.22, 1, 0.36, 1] as const;

export const easeInOut = [0.65, 0, 0.35, 1] as const;

/** For things that slide to a new place: damped, no bounce. */
export const slideSpring: Transition = {
    type: 'spring',
    stiffness: 500,
    damping: 40,
    mass: 1,
};

/** For height changes and items entering or leaving a list. */
export const resize: Transition = {
    duration: duration.slow,
    ease: easeOut,
};

/** For content swapping in place, such as the steps of a dialog. */
export const crossfade: Transition = {
    duration: duration.base,
    ease: easeOut,
};

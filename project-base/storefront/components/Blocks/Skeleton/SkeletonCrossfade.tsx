import { AnimatePresence, m, useReducedMotion } from 'framer-motion';
import { ReactNode, useEffect, useRef, useState } from 'react';

type SkeletonCrossfadeProps = {
    isSkeletonVisible: boolean;
    skeleton: ReactNode;
};

const SKELETON_EXIT_DURATION_SECONDS = 0.2;

// An exit interrupted by React (e.g. when the loaded content suspends right after the skeleton hides) never
// completes and would leave the skeleton over the content, so a skeleton still present after this time is dropped.
const STUCK_SKELETON_TIMEOUT_MS = 500;

/**
 * Shows the skeleton while the content loads and then fades the skeleton out over the loaded content, which renders
 * under it right away (the page loading and the product list use the same transition). A skeleton that is not visible
 * on the first render does not animate, so server-rendered content appears as it is.
 */
export const SkeletonCrossfade: FC<SkeletonCrossfadeProps> = ({ isSkeletonVisible, skeleton, children }) => {
    const shouldReduceMotion = useReducedMotion();
    const [presenceKey, setPresenceKey] = useState(0);
    const hasExitingSkeletonRef = useRef(isSkeletonVisible);

    useEffect(() => {
        if (isSkeletonVisible) {
            hasExitingSkeletonRef.current = true;

            return undefined;
        }

        if (!hasExitingSkeletonRef.current) {
            return undefined;
        }

        // a new presence has no children, so it drops the skeleton whose exit did not finish
        const dropStuckSkeleton = () => {
            hasExitingSkeletonRef.current = false;
            setPresenceKey((key) => key + 1);
        };
        const timeout = setTimeout(dropStuckSkeleton, STUCK_SKELETON_TIMEOUT_MS);

        return () => clearTimeout(timeout);
    }, [isSkeletonVisible]);

    return (
        <div aria-busy={isSkeletonVisible} className="relative isolate">
            <AnimatePresence key={presenceKey} initial={false} mode="popLayout">
                {isSkeletonVisible && (
                    <m.div
                        key="skeleton"
                        animate={{ opacity: 1 }}
                        aria-hidden="true"
                        className="pointer-events-none relative z-above bg-background-default"
                        exit={{ opacity: 0 }}
                        inert
                        transition={{
                            duration: shouldReduceMotion ? 0 : SKELETON_EXIT_DURATION_SECONDS,
                            ease: 'easeOut',
                        }}
                    >
                        {skeleton}
                    </m.div>
                )}
            </AnimatePresence>

            {!isSkeletonVisible && <div>{children}</div>}
        </div>
    );
};

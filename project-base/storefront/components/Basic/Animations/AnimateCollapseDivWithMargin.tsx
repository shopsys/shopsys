import { TIDs } from 'cypress/tids';
import { HTMLMotionProps, m, useReducedMotion } from 'framer-motion';
import { collapseExpandAnimationWithMargin } from 'utils/animations/animationVariants';

export const AnimateCollapseDivWithMargin: FC<
    HTMLMotionProps<'div'> & { tid?: TIDs; keyName?: string; disableAnimation?: boolean }
> = ({ children, className, keyName, tid, disableAnimation, ...props }) => {
    const shouldReduceMotion = useReducedMotion();

    return (
        <m.div
            key={keyName}
            animate="open"
            className={className}
            data-tid={tid}
            exit="closed"
            initial="closed"
            variants={disableAnimation || shouldReduceMotion ? undefined : collapseExpandAnimationWithMargin}
            {...props}
        >
            {children}
        </m.div>
    );
};

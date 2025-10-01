import { TIDs } from 'cypress/tids';
import { HTMLMotionProps, m } from 'framer-motion';
import { collapseExpandAnimationWithMargin } from 'utils/animations/animationVariants';

export const AnimateCollapseDivWithMargin: FC<
    HTMLMotionProps<'div'> & { tid?: TIDs; keyName?: string; disableAnimation?: boolean }
> = ({ children, className, keyName, tid, disableAnimation, ...props }) => (
    <m.div
        key={keyName}
        animate="open"
        className={className}
        data-tid={tid}
        exit="closed"
        initial="closed"
        variants={disableAnimation ? undefined : collapseExpandAnimationWithMargin}
        {...props}
    >
        {children}
    </m.div>
);

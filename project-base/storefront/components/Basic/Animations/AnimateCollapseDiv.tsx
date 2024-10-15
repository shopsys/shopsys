import { TIDs } from 'cypress/tids';
import { HTMLMotionProps, m } from 'framer-motion';
import { collapseExpandAnimation } from 'utils/animations/animationVariants';

export const AnimateCollapseDiv: FC<HTMLMotionProps<'div'> & { tid?: TIDs; keyName?: string }> = ({
    children,
    className,
    keyName,
    tid,
    ...props
}) => (
    <m.div
        key={keyName}
        animate="open"
        className={className}
        exit="closed"
        initial="closed"
        tid={tid}
        variants={collapseExpandAnimation}
        {...props}
    >
        {children}
    </m.div>
);

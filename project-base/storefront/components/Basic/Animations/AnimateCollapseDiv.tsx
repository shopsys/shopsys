import { TIDs } from 'cypress/tids';
import { HTMLMotionProps, motion } from 'framer-motion';
import { collapseExpandAnimation } from 'utils/animations/animationVariants';

export const AnimateCollapseDiv: FC<HTMLMotionProps<'div'> & { tid?: TIDs; keyName?: string }> = ({
    children,
    className,
    keyName,
    tid,
    ...props
}) => (
    <motion.div
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
    </motion.div>
);

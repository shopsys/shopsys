import { TIDs } from 'cypress/tids';
import { HTMLMotionProps, motion } from 'framer-motion';
import { fadeAnimation } from 'utils/animations/animationVariants';

export const AnimateNavigationMenu: FC<
    HTMLMotionProps<'div'> & { tid?: TIDs; keyName?: string; disableAnimation: boolean }
> = ({ children, className, keyName, tid, disableAnimation, ...props }) => (
    <motion.div
        key={keyName}
        animate="visible"
        className={className}
        exit="hidden"
        initial="hidden"
        tid={tid}
        variants={disableAnimation ? undefined : fadeAnimation}
        {...props}
    >
        {children}
    </motion.div>
);

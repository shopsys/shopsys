import { TIDs } from 'cypress/tids';
import { HTMLMotionProps, m } from 'framer-motion';
import { fadeAnimation } from 'utils/animations/animationVariants';

export const AnimateNavigationMenu: FC<
    HTMLMotionProps<'div'> & { tid?: TIDs; keyName?: string; disableAnimation: boolean }
> = ({ children, className, keyName, tid, disableAnimation, ...props }) => (
    <m.div
        key={keyName}
        animate="visible"
        className={className}
        data-tid={tid}
        exit="hidden"
        initial="hidden"
        variants={disableAnimation ? undefined : fadeAnimation}
        {...props}
    >
        {children}
    </m.div>
);

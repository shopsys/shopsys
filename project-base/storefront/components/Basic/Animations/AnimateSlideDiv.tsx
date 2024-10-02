import { TIDs } from 'cypress/tids';
import { HTMLMotionProps, m } from 'framer-motion';
import { slideAnimation } from 'utils/animations/animationVariants';

export const AnimateSlideDiv: FC<
    HTMLMotionProps<'div'> & { tid?: TIDs; keyName?: string; direction?: 'right' | 'left' }
> = ({ children, className, keyName, direction, ...props }) => (
    <m.div
        key={keyName}
        animate="visible"
        className={className}
        exit={direction === 'right' ? 'hiddenLeft' : 'hiddenRight'}
        initial={direction === 'right' ? 'hiddenRight' : 'hiddenLeft'}
        transition={{ duration: 0.2 }}
        variants={slideAnimation}
        {...props}
    >
        {children}
    </m.div>
);

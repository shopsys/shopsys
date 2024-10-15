import { HTMLMotionProps, m } from 'framer-motion';

export const AnimateAppearSlideY: FC<HTMLMotionProps<'div'> & { keyName?: string }> = ({
    children,
    className,
    keyName,
    ...props
}) => (
    <m.div
        key={keyName}
        layout
        animate={{ opacity: 1, y: 0 }}
        className={className}
        exit={{ opacity: 0, y: '-10px' }}
        initial={{ opacity: 0, y: '-10px' }}
        transition={{ duration: 0.125, type: 'keyframes' }}
        {...props}
    >
        {children}
    </m.div>
);

import { HTMLMotionProps, motion } from 'framer-motion';

export const AnimateAppearDiv: FC<HTMLMotionProps<'div'> & { keyName?: string }> = ({
    children,
    className,
    keyName,
    ...props
}) => (
    <motion.div
        key={keyName}
        animate={{ opacity: 1, scale: 1 }}
        className={className}
        exit={{ opacity: 0, scale: 0.2 }}
        initial={{ opacity: 0, scale: 0.2 }}
        transition={{ duration: 0.2 }}
        {...props}
    >
        {children}
    </motion.div>
);

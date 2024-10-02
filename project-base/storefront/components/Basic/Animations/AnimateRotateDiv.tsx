import { HTMLMotionProps, m } from 'framer-motion';

export const AnimateRotateDiv: FC<HTMLMotionProps<'div'> & { condition?: boolean; keyName?: string }> = ({
    children,
    className,
    condition,
    keyName,
    ...props
}) => (
    <m.div
        key={keyName}
        animate={{ rotate: condition ? 180 : 0 }}
        className={className}
        transition={{ type: 'tween', duration: 0.2 }}
        {...props}
    >
        {children}
    </m.div>
);

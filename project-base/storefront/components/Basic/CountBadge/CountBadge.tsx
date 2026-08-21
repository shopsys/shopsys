import { HTMLAttributes } from 'react';
import { twMergeCustom } from 'utils/twMerge';

export const CountBadge: FC<HTMLAttributes<HTMLSpanElement>> = ({ children, className, ...props }) => (
    <span
        className={twMergeCustom(
            'flex h-5 min-w-5 items-center justify-center rounded-full px-0.5 pt-0.5 font-bold font-secondary text-xs leading-normal',
            className,
        )}
        {...props}
    >
        {children}
    </span>
);

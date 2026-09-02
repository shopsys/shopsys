import { type ElementType } from 'react';
import { twMergeCustom } from 'utils/twMerge';

export type StatusBadgeVariant = 'error' | 'success' | 'warning';

type StatusBadgeProps = {
    icon?: ElementType;
    variant: StatusBadgeVariant;
};

export const StatusBadge: FC<StatusBadgeProps> = ({ children, className, icon: Icon, variant }) => {
    const variantClasses: Record<StatusBadgeVariant, string> = {
        error: 'bg-status-badge-bg-error text-status-badge-text-error',
        success: 'bg-status-badge-bg-success text-status-badge-text-success',
        warning: 'bg-status-badge-bg-warning text-status-badge-text-warning',
    };

    return (
        <span
            className={twMergeCustom(
                'inline-flex w-fit items-center gap-1 text-nowrap rounded-lg px-2 py-1 font-bold font-secondary text-xs',
                variantClasses[variant],
                className,
            )}
        >
            {Icon && <Icon aria-hidden="true" className="size-3 **:stroke-[2.5]" focusable="false" />}
            {children}
        </span>
    );
};

import { ReactNode } from 'react';
import { twMergeCustom } from 'utils/twMerge';

export type AlertVariant = 'error' | 'info' | 'success' | 'warning';

type AlertProps = {
    icon?: React.ElementType;
    title?: ReactNode;
    variant: AlertVariant;
    tid?: string;
    children?: ReactNode;
};

export const Alert: FC<AlertProps> = ({ icon: Icon, title, variant, tid, children, className }) => {
    const alertVariantClasses: Record<AlertVariant, string> = {
        error: 'border-alert-border-error bg-alert-bg-error text-alert-text-error',
        info: 'border-alert-border-info bg-alert-bg-info text-alert-text-info',
        success: 'border-alert-border-success bg-alert-bg-success text-alert-text-success',
        warning: 'border-alert-border-warning bg-alert-bg-warning text-alert-text-warning',
    };
    const iconVariantClasses: Record<AlertVariant, string> = {
        error: 'text-alert-icon-error',
        info: 'text-alert-icon-info',
        success: 'text-alert-icon-success',
        warning: 'text-alert-icon-warning',
    };

    return (
        <div
            className={twMergeCustom(
                'flex w-full items-start gap-3 rounded-xl border-1 p-4 text-left text-sm sm:p-5',
                alertVariantClasses[variant],
                className,
            )}
            data-tid={tid}
        >
            {Icon && (
                <span
                    className={twMergeCustom(
                        'flex size-9 shrink-0 items-center justify-center rounded-full bg-background-default',
                        iconVariantClasses[variant],
                    )}
                >
                    <Icon aria-hidden="true" className="size-5" focusable="false" />
                </span>
            )}

            <div className="flex flex-col gap-1">
                {title && <span className="font-semibold">{title}</span>}
                {children}
            </div>
        </div>
    );
};

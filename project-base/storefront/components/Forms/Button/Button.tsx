import { ButtonHTMLAttributes, forwardRef } from 'react';
import { twMergeCustom } from 'utils/twMerge';
import { getButtonClassName } from './buttonUtils';

export type ButtonBaseProps = {
    hasDisabledLook?: boolean;
    hasDisabledCursor?: boolean;
    size?: 'small' | 'medium' | 'large' | 'xlarge';
    variant?: 'primary' | 'secondary' | 'tertiary' | 'danger' | 'inverted';
    shouldShowSpinner?: boolean;
};

export type ButtonProps = ButtonBaseProps & ButtonHTMLAttributes<HTMLButtonElement>;

export const Button: FC<ButtonProps> = forwardRef(
    (
        {
            children,
            tid,
            className,
            disabled,
            hasDisabledLook,
            hasDisabledCursor,
            size = 'medium',
            variant = 'primary',
            type = 'button',
            ...props
        },
        _,
    ) => {
        return (
            <button
                data-tid={tid}
                disabled={disabled}
                tabIndex={0}
                type={type}
                className={twMergeCustom(
                    getButtonClassName(variant, size, hasDisabledLook, hasDisabledCursor),
                    className,
                )}
                {...props}
            >
                {children}
            </button>
        );
    },
);

Button.displayName = 'Button';

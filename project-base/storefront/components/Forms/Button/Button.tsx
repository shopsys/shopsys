import { ButtonHTMLAttributes, forwardRef } from 'react';
import { twJoin } from 'tailwind-merge';
import { twMergeCustom } from 'utils/twMerge';

export type ButtonBaseProps = {
    hasDisabledLook?: boolean;
    hasDisabledCursor?: boolean;
    size?: 'small' | 'medium' | 'large' | 'xlarge';
    variant?: 'primary' | 'secondary' | 'inverted' | 'transparent';
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

export const getButtonIconClassName = (size: ButtonBaseProps['size']) =>
    twJoin(
        size === 'small' && 'size-4',
        size === 'medium' && 'size-4 md:size-5',
        size === 'large' && 'size-5',
        size === 'xlarge' && 'size-6',
    );

export const getButtonClassName = (
    variant: ButtonBaseProps['variant'],
    size: ButtonBaseProps['size'],
    hasDisabledLook: ButtonBaseProps['hasDisabledLook'],
    hasDisabledCursor: ButtonBaseProps['hasDisabledCursor'],
) => {
    return twJoin(
        'inline-flex h-fit w-auto cursor-pointer items-center justify-center gap-2 rounded-button text-center font-bold font-secondary transition-all hover:no-underline',
        'outline-2 -outline-offset-2',
        size === 'small' && 'px-3 py-2.5 text-xs',
        size === 'medium' && 'px-3 py-2.5 text-xs sm:px-4 sm:py-2 sm:text-sm',
        size === 'large' && 'px-4 py-2 text-sm sm:py-2.5',
        size === 'xlarge' && 'px-4 py-2.5 text-sm sm:px-5 sm:py-3.5 sm:text-lg',
        variant === 'primary' && [
            'bg-button-primary-bg-default text-button-primary-text-default outline-button-primary-border-default',
            !hasDisabledLook &&
                'hover:bg-button-primary-bg-hovered hover:text-button-primary-text-hovered hover:outline-button-primary-border-hovered',
            !hasDisabledLook &&
                'active:bg-button-primary-bg-active active:text-button-primary-text-active active:outline-button-primary-border-active',
            hasDisabledLook &&
                'bg-button-primary-bg-disabled text-button-primary-text-disabled outline-button-primary-border-disabled',
        ],
        variant === 'secondary' && [
            'bg-button-secondary-bg-default text-button-secondary-text-default outline-button-secondary-border-default',
            !hasDisabledLook &&
                'hover:bg-button-secondary-bg-hovered hover:text-button-secondary-text-hovered hover:outline-button-secondary-border-hovered',
            !hasDisabledLook &&
                'active:bg-button-secondary-bg-active active:text-button-secondary-text-active active:outline-button-secondary-border-active',
            hasDisabledLook &&
                'bg-button-secondary-bg-disabled text-button-secondary-text-disabled outline-button-secondary-border-disabled',
        ],
        variant === 'inverted' && [
            'bg-button-inverted-bg-default text-button-inverted-text-default outline-button-inverted-border-default',
            !hasDisabledLook &&
                'hover:bg-button-inverted-bg-hovered hover:text-button-inverted-text-hovered hover:outline-button-inverted-border-hovered',
            !hasDisabledLook &&
                'active:bg-button-inverted-bg-active active:text-button-inverted-text-active active:outline-button-inverted-border-active',
            hasDisabledLook &&
                'bg-button-inverted-bg-disabled text-button-inverted-text-disabled outline-button-inverted-border-disabled',
        ],
        variant === 'transparent' && [
            'bg-button-transparent-bg-default text-button-transparent-text-default outline-button-transparent-border-default',
            !hasDisabledLook &&
                'hover:bg-button-transparent-bg-hovered hover:text-button-transparent-text-hovered hover:outline-button-transparent-border-disabled',
            !hasDisabledLook &&
                'active:bg-button-transparent-bg-active active:text-button-transparent-text-active active:outline-button-transparent-border-active',
            hasDisabledLook &&
                'bg-button-transparent-bg-disabled text-button-transparent-text-disabled outline-button-transparent-border-disabled',
        ],
        (hasDisabledLook || hasDisabledCursor) && 'cursor-no-drop',
    );
};

Button.displayName = 'Button';

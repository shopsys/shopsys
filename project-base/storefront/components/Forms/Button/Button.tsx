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
        // eslint-disable-next-line @typescript-eslint/no-unused-vars
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

export const getButtonClassName = (
    variant: ButtonBaseProps['variant'],
    size: ButtonBaseProps['size'],
    hasDisabledLook: ButtonBaseProps['hasDisabledLook'],
    hasDisabledCursor: ButtonBaseProps['hasDisabledCursor'],
) => {
    return twJoin(
        'inline-flex w-auto h-fit cursor-pointer items-center justify-center gap-2 rounded-button text-center font-bold font-secondary transition-all hover:no-underline',
        'outline-2 -outline-offset-2',
        size === 'small' && 'px-3 py-2.5 text-xs',
        size === 'medium' && 'px-3 py-2.5 text-xs sm:px-4 sm:py-2 sm:text-sm',
        size === 'large' && 'px-4 py-2 text-sm sm:py-2.5',
        size === 'xlarge' && 'px-4 py-2.5 text-sm sm:px-5 sm:py-3.5 sm:text-lg',
        variant === 'primary' && [
            'outline-button-primary-border-default bg-button-primary-bg-default text-button-primary-text-default',
            !hasDisabledLook &&
                'hover:outline-button-primary-border-hovered hover:bg-button-primary-bg-hovered hover:text-button-primary-text-hovered',
            !hasDisabledLook &&
                'active:outline-button-primary-border-active active:bg-button-primary-bg-active active:text-button-primary-text-active',
            hasDisabledLook &&
                'outline-button-primary-border-disabled bg-button-primary-bg-disabled text-button-primary-text-disabled',
        ],
        variant === 'secondary' && [
            'outline-button-secondary-border-default bg-button-secondary-bg-default text-button-secondary-text-default',
            !hasDisabledLook &&
                'hover:outline-button-secondary-border-hovered hover:bg-button-secondary-bg-hovered hover:text-button-secondary-text-hovered',
            !hasDisabledLook &&
                'active:outline-button-secondary-border-active active:bg-button-secondary-bg-active active:text-button-secondary-text-active',
            hasDisabledLook &&
                'outline-button-secondary-border-disabled bg-button-secondary-bg-disabled text-button-secondary-text-disabled',
        ],
        variant === 'inverted' && [
            'outline-button-inverted-border-default bg-button-inverted-bg-default text-button-inverted-text-default',
            !hasDisabledLook &&
                'hover:outline-button-inverted-border-hovered hover:bg-button-inverted-bg-hovered hover:text-button-inverted-text-hovered',
            !hasDisabledLook &&
                'active:outline-button-inverted-border-active active:bg-button-inverted-bg-active active:text-button-inverted-text-active',
            hasDisabledLook &&
                'outline-button-inverted-border-disabled bg-button-inverted-bg-disabled text-button-inverted-text-disabled',
        ],
        variant === 'transparent' && [
            'outline-button-transparent-border-default bg-button-transparent-bg-default text-button-transparent-text-default',
            !hasDisabledLook &&
                'hover:outline-button-transparent-border-disabled hover:bg-button-transparent-bg-hovered hover:text-button-transparent-text-hovered',
            !hasDisabledLook &&
                'active:outline-button-transparent-border-active active:bg-button-transparent-bg-active active:text-button-transparent-text-active',
            hasDisabledLook &&
                'outline-button-transparent-border-disabled bg-button-transparent-bg-disabled text-button-transparent-text-disabled',
        ],
        (hasDisabledLook || hasDisabledCursor) && 'cursor-no-drop',
    );
};

Button.displayName = 'Button';

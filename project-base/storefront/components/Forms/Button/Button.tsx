import { ButtonHTMLAttributes, forwardRef } from 'react';
import { twJoin } from 'tailwind-merge';
import { twMergeCustom } from 'utils/twMerge';

type NativeButtonProps = Omit<ButtonHTMLAttributes<HTMLButtonElement>, 'disabled'>;

export type ButtonBaseProps = {
    isDisabled?: boolean;
    isWithDisabledLook?: boolean;
    size?: 'small' | 'medium' | 'large' | 'xlarge';
    variant?: 'primary' | 'secondary' | 'inverted' | 'transparent';
};

export type ButtonProps = ButtonBaseProps & NativeButtonProps;

export const Button: FC<ButtonProps> = forwardRef(
    (
        {
            children,
            tid,
            className,
            isDisabled,
            isWithDisabledLook,
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
                className={twMergeCustom(getButtonClassName(variant, size, isDisabled, isWithDisabledLook), className)}
                data-tid={tid}
                tabIndex={0}
                type={type}
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
    isDisabled: ButtonBaseProps['isDisabled'],
    isWithDisabledLook: ButtonBaseProps['isWithDisabledLook'],
) => {
    return twJoin(
        'inline-flex w-auto h-fit cursor-pointer items-center justify-center gap-2 rounded-button text-center font-bold font-secondary transition-all hover:no-underline',
        'outline-2 outline-offset-[-2px]',
        size === 'small' && 'px-3 py-2.5 text-xs',
        size === 'medium' && 'px-3 py-2.5 text-xs sm:px-4 sm:py-2 sm:text-sm',
        size === 'large' && 'px-4 py-2 text-sm sm:py-2.5',
        size === 'xlarge' && 'px-4 py-2.5 text-sm sm:px-5 sm:py-3.5 sm:text-lg',
        variant === 'primary' && [
            'outline-button-primary-border-default bg-button-primary-bg-default text-button-primary-text-default',
            !isDisabled &&
                'hover:outline-button-primary-border-hovered hover:bg-button-primary-bg-hovered hover:text-button-primary-text-hovered',
            !isDisabled &&
                'active:outline-button-primary-border-active active:bg-button-primary-bg-active active:text-button-primary-text-active',
            isDisabled &&
                'outline-button-primary-border-disabled bg-button-primary-bg-disabled text-button-primary-text-disabled',
        ],
        variant === 'secondary' && [
            'outline-button-secondary-border-default bg-button-secondary-bg-default text-button-secondary-text-default',
            !isDisabled &&
                'hover:outline-button-secondary-border-hovered hover:bg-button-secondary-bg-hovered hover:text-button-secondary-text-hovered',
            !isDisabled &&
                'active:outline-button-secondary-border-active active:bg-button-secondary-bg-active active:text-button-secondary-text-active',
            isDisabled &&
                'outline-button-secondary-border-disabled bg-button-secondary-bg-disabled text-button-secondary-text-disabled',
        ],
        variant === 'inverted' && [
            'outline-button-inverted-border-default bg-button-inverted-bg-default text-button-inverted-text-default',
            !isDisabled &&
                'hover:outline-button-inverted-border-hovered hover:bg-button-inverted-bg-hovered hover:text-button-inverted-text-hovered',
            !isDisabled &&
                'active:outline-button-inverted-border-active active:bg-button-inverted-bg-active active:text-button-inverted-text-active',
            isDisabled &&
                'outline-button-inverted-border-disabled bg-button-inverted-bg-disabled text-button-inverted-text-disabled',
        ],
        variant === 'transparent' && [
            'outline-button-transparent-border-default bg-button-transparent-bg-default text-button-transparent-text-default',
            !isDisabled &&
                'hover:outline-button-transparent-border-disabled hover:bg-button-transparent-bg-hovered hover:text-button-transparent-text-hovered',
            !isDisabled &&
                'active:outline-button-transparent-border-active active:bg-button-transparent-bg-active active:text-button-transparent-text-active',
            isDisabled &&
                'outline-button-transparent-border-disabled bg-button-transparent-bg-disabled text-button-transparent-text-disabled',
        ],
        (isDisabled || isWithDisabledLook) && 'cursor-no-drop',
    );
};

Button.displayName = 'Button';

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
                tid={tid}
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
        'inline-flex w-auto h-fit cursor-pointer items-center justify-center gap-2 rounded-md text-center font-bold font-secondary transition-all hover:no-underline',
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
            'outline-actionSecondaryBorder bg-actionSecondaryBackground text-actionSecondaryText',
            !isDisabled &&
                'hover:outline-actionSecondaryBorderHovered hover:bg-actionSecondaryBackgroundHovered hover:text-actionSecondaryTextHovered',
            !isDisabled &&
                'active:outline-actionSecondaryBorderActive active:bg-actionSecondaryBackgroundActive active:text-actionSecondaryTextActive',
            isDisabled &&
                'outline-actionSecondaryBorderDisabled bg-actionSecondaryBackgroundDisabled text-actionSecondaryTextDisabled',
        ],
        variant === 'inverted' && [
            'outline-actionInvertedBorder bg-actionInvertedBackground text-actionInvertedText',
            !isDisabled &&
                'hover:outline-actionInvertedBorderHovered hover:bg-actionInvertedBackgroundHovered hover:text-actionInvertedTextHovered',
            !isDisabled &&
                'active:outline-actionInvertedBorderActive active:bg-actionInvertedBackgroundActive active:text-actionInvertedTextActive',
            isDisabled &&
                'outline-actionInvertedBorderDisabled bg-actionInvertedBackgroundDisabled text-actionInvertedTextDisabled',
        ],
        variant === 'transparent' && [
            'outline-1 outline-offset-[-1px] outline-actionTransparentBorder bg-actionTransparentBackground text-actionTransparentText',
            !isDisabled &&
                'hover:outline-actionTransparentBorderHovered hover:bg-actionTransparentBackgroundHovered hover:text-actionTransparentTextHovered',
            !isDisabled &&
                'active:outline-actionTransparentBorderActive active:bg-actionTransparentBackgroundActive active:text-actionTransparentTextActive',
            isDisabled &&
                'outline-actionTransparentBorderDisabled bg-actionTransparentBackgroundDisabled text-actionTransparentTextDisabled',
        ],
        (isDisabled || isWithDisabledLook) && 'cursor-no-drop',
    );
};

Button.displayName = 'Button';

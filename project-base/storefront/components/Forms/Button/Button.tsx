// eslint-disable-next-line no-restricted-imports
import { ButtonHTMLAttributes, forwardRef } from 'react';
import { twJoin } from 'tailwind-merge';
import { twMergeCustom } from 'utils/twMerge';

type NativeButtonProps = Omit<ButtonHTMLAttributes<HTMLButtonElement>, 'disabled'>;

export type ButtonBaseProps = {
    isDisabled?: boolean;
    isWithDisabledLook?: boolean;
    size?: 'small' | 'medium' | 'large';
    variant?: 'primary' | 'secondary' | 'inverted';
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
        'inline-flex w-auto h-fit cursor-pointer items-center justify-center gap-2 rounded-md text-center font-bold font-secondary outline-none transition-all hover:no-underline',
        'outline-2 outline-offset-[-2px]',
        size === 'small' && 'px-4 py-2 text-xs',
        size === 'medium' && 'p-2 text-xs sm:px-4 sm:text-sm',
        size === 'large' && 'px-5 py-3 text-lg',
        variant === 'primary' && [
            'outline-actionPrimaryBorder bg-actionPrimaryBackground text-actionPrimaryText',
            !isDisabled &&
                'hover:outline-actionPrimaryBorderHovered hover:bg-actionPrimaryBackgroundHovered hover:text-actionPrimaryTextHovered',
            !isDisabled &&
                'active:outline-actionPrimaryBorderActive active:bg-actionPrimaryBackgroundActive active:text-actionPrimaryTextActive',
            isDisabled &&
                'outline-actionPrimaryBorderDisabled bg-actionPrimaryBackgroundDisabled text-actionPrimaryTextDisabled',
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
        (isDisabled || isWithDisabledLook) && 'cursor-no-drop',
    );
};

Button.displayName = 'Button';

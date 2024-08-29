// eslint-disable-next-line no-restricted-imports
import { ButtonHTMLAttributes, forwardRef } from 'react';
import { twJoin } from 'tailwind-merge';
import { twMergeCustom } from 'utils/twMerge';

type NativeButtonProps = Omit<ButtonHTMLAttributes<HTMLButtonElement>, 'disabled'>;

export type ButtonBaseProps = {
    isDisabled?: boolean;
    isWithDisabledLook?: boolean;
    size?: 'small' | 'medium';
    variant?: 'primary' | 'inverted';
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
        'inline-flex w-auto h-fit cursor-pointer items-center justify-center gap-2 rounded-lg text-center font-bold outline-none transition-all hover:no-underline border-2',
        size === 'small' && 'py-1 px-4 text-sm',
        size === 'medium' && 'py-3 px-8',
        variant === 'primary' && [
            'border-actionPrimaryBorder bg-actionPrimaryBackground text-actionPrimaryText',
            'hover:border-actionPrimaryBorderHovered hover:bg-actionPrimaryBackgroundHovered hover:text-actionPrimaryTextHovered',
            'active:border-actionPrimaryBorderActive active:bg-actionPrimaryBackgroundActive active:text-actionPrimaryTextActive',
            isDisabled &&
                'border-actionPrimaryBorderDisabled bg-actionPrimaryBackgroundDisabled text-actionPrimaryTextDisabled',
        ],
        variant === 'inverted' && [
            'border-actionInvertedBorder bg-actionInvertedBackground text-actionInvertedText',
            'hover:border-actionInvertedBorderHovered hover:bg-actionInvertedBackgroundHovered hover:text-actionInvertedTextHovered',
            'active:border-actionInvertedBorderActive active:bg-actionInvertedBackgroundActive active:text-actionInvertedTextActive',
            isDisabled &&
                'border-actionInvertedBorderDisabled bg-actionInvertedBackgroundDisabled text-actionInvertedTextDisabled',
        ],
        (isDisabled || isWithDisabledLook) && 'cursor-no-drop',
        isDisabled && 'pointer-events-none',
    );
};

Button.displayName = 'Button';

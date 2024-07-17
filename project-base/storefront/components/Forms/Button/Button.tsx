// eslint-disable-next-line no-restricted-imports
import { ButtonHTMLAttributes, forwardRef } from 'react';
import { twJoin } from 'tailwind-merge';
import { twMergeCustom } from 'utils/twMerge';

type NativeButtonProps = Omit<ButtonHTMLAttributes<HTMLButtonElement>, 'disabled'>;

export type ButtonBaseProps = {
    isDisabled?: boolean;
    isWithDisabledLook?: boolean;
    size?: 'small' | 'medium';
    variant?: 'primary' | 'primaryOutlined' | 'secondary' | 'secondaryOutlined';
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
            type,
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
        'inline-flex w-auto h-fit cursor-pointer items-center justify-center gap-2 rounded text-center font-bold outline-none transition-all hover:no-underline border-2 hover:text-white active:scale-95',
        size === 'small' && 'py-1 px-4 text-sm',
        size === 'medium' && 'py-3 px-8',
        variant === 'primary' && [
            'border-secondary bg-secondary text-white hover:bg-secondaryLight hover:border-secondaryLight',
            isDisabled && 'bg-secondarySlate border-secondarySlate text-skyBlue',
        ],
        variant === 'primaryOutlined' && [
            'border-secondary border-2 text-secondary hover:border-secondaryLight hover:text-secondaryLight',
            isDisabled && 'border-secondarySlate text-secondarySlate',
        ],
        variant === 'secondary' && [
            'border-primaryDark bg-primaryDark text-white hover:bg-skyBlue hover:border-skyBlue active:border-dark active:bg-dark',
            isDisabled && 'border-graySlate bg-graySlate',
        ],
        variant === 'secondaryOutlined' && [
            'border-primaryDark border-2 text-primaryDark hover:border-skyBlue hover:text-skyBlue active:border-dark active:text-dark',
            isDisabled && 'border-graySlate text-graySlate',
        ],
        (isDisabled || isWithDisabledLook) && 'cursor-no-drop',
        isDisabled && 'pointer-events-none',
    );
};

Button.displayName = 'Button';

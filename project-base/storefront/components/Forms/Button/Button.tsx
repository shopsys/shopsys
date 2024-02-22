import { twMergeCustom } from 'helpers/twMerge';
import { ButtonHTMLAttributes, forwardRef } from 'react';

type NativeButtonProps = Omit<ButtonHTMLAttributes<HTMLButtonElement>, 'disabled'>;

export type ButtonProps = NativeButtonProps & {
    isDisabled?: boolean;
    isWithDisabledLook?: boolean;
    size?: 'small';
    variant?: 'primary' | 'secondary';
};

export const Button: FC<ButtonProps> = forwardRef(
    (
        { children, tid, className, isDisabled, isWithDisabledLook, size, variant, ...props },
        // eslint-disable-next-line @typescript-eslint/no-unused-vars
        _,
    ) => {
        return (
            <button
                tid={tid}
                type="button"
                className={twMergeCustom(
                    'inline-flex w-auto cursor-pointer items-center justify-center gap-2 rounded text-center font-bold uppercase outline-none transition-all hover:no-underline',
                    size === 'small' ? 'py-1 px-4 text-sm' : 'py-3 px-4 text-base vl:px-8',
                    !variant && 'bg-orange text-white hover:bg-orangeDarker hover:text-white',
                    variant === 'primary' && 'bg-primary text-white hover:bg-primaryDarker hover:text-white',
                    variant === 'secondary' && 'bg-orangeLight text-black hover:bg-white hover:text-black',
                    (isDisabled || isWithDisabledLook) && 'cursor-no-drop opacity-50',
                    isDisabled && 'pointer-events-none',
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

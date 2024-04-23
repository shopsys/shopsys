import { ButtonHTMLAttributes, forwardRef } from 'react';
import { twMergeCustom } from 'utils/twMerge';

type NativeButtonProps = Omit<ButtonHTMLAttributes<HTMLButtonElement>, 'disabled'>;

export type ButtonProps = NativeButtonProps & {
    isDisabled?: boolean;
    isWithDisabledLook?: boolean;
    size?: 'small';
    variant?: 'primary' | 'secondary';
};

export const Button: FC<ButtonProps> = forwardRef(
    (
        { children, tid, className, isDisabled, isWithDisabledLook, size, variant = 'primary', ...props },
        // eslint-disable-next-line @typescript-eslint/no-unused-vars
        _,
    ) => {
        return (
            <button
                tid={tid}
                type="button"
                className={twMergeCustom(
                    'inline-flex w-auto cursor-pointer items-center justify-center gap-2 rounded text-center font-bold uppercase outline-none transition-all hover:no-underline border-2 hover:text-white max-vl:active:scale-105',
                    size === 'small' ? 'py-1 px-4 text-sm' : 'py-3 px-8',
                    variant === 'primary' &&
                        'border-secondary bg-secondary text-white hover:bg-secondaryLight hover:border-secondaryLight',
                    variant === 'secondary' &&
                        'border-secondary border-2 text-secondary hover:border-secondaryLight hover:text-secondaryLight',
                    (isDisabled || isWithDisabledLook) &&
                        'cursor-no-drop bg-secondarySlate border-secondarySlate text-skyBlue',
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

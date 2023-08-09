import { ButtonHTMLAttributes, forwardRef } from 'react';
import { useFormContext } from 'react-hook-form';
import { twMergeCustom } from 'helpers/visual/twMerge';

type NativeButtonProps = Omit<ButtonHTMLAttributes<HTMLButtonElement>, 'type' | 'disabled'>;

type Props = NativeButtonProps &
    Omit<NativeButtonProps, 'type' | 'disabled'> & {
        type?: 'button' | 'submit' | 'reset';
        isDisabled?: boolean;
        isWithDisabledLook?: boolean;
        size?: 'small';
        variant?: 'primary' | 'secondary';
        isRounder?: boolean;
    };

export const Button: FC<Props> = forwardRef(
    (
        {
            children,
            type = 'button',
            dataTestId,
            className,
            isDisabled: isDisabledDefault,
            isWithDisabledLook,
            isRounder,
            size,
            variant,
            ...props
        },
        // eslint-disable-next-line @typescript-eslint/no-unused-vars
        _,
    ) => {
        const formProviderMethods = useFormContext();

        // formProviderMethods may be null probably when it is not used in FormProvider context - see https://github.com/react-hook-form/react-hook-form/discussions/3894
        // eslint-disable-next-line @typescript-eslint/no-unnecessary-condition
        const isDisabled = isDisabledDefault || (type === 'submit' && formProviderMethods?.formState.isSubmitting);

        return (
            <button
                className={twMergeCustom(
                    'inline-flex w-auto cursor-pointer items-center justify-center gap-2 text-center font-bold uppercase outline-none transition-all hover:no-underline',
                    size === 'small' ? 'py-1 px-4 text-sm' : 'py-3 px-8 text-base',
                    !variant && 'bg-orange text-white hover:bg-orangeDarker hover:text-white',
                    variant === 'primary' && 'bg-primary text-white hover:bg-primaryDarker hover:text-white',
                    variant === 'secondary' && 'bg-orangeLight text-black hover:bg-white hover:text-black',
                    (isDisabled || isWithDisabledLook) && 'cursor-no-drop opacity-50',
                    isDisabled && 'pointer-events-none',
                    isRounder ? 'rounded-xl' : 'rounded',
                    className,
                )}
                type={type}
                data-testid={dataTestId}
                {...props}
            >
                {children}
            </button>
        );
    },
);

Button.displayName = 'Button';

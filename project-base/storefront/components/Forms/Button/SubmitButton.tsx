import { Loader } from 'components/Basic/Loader/Loader';
import { forwardRef } from 'react';
import { useFormContext } from 'react-hook-form';
import { Button, ButtonProps } from './Button';

export const SubmitButton: FC<ButtonProps> = forwardRef(
    ({ children, hasDisabledLook: isDisabledDefault, disabled, shouldShowSpinner, ...props }, _) => {
        const formProviderMethods = useFormContext();

        // formProviderMethods may be null probably when it is not used in FormProvider context - see https://github.com/react-hook-form/react-hook-form/discussions/3894
        const isFormSubmitting = formProviderMethods?.formState.isSubmitting || false;
        const hasDisabledLook = isDisabledDefault || isFormSubmitting;
        const isDisabled = disabled || isFormSubmitting;

        return (
            <div className="relative w-fit">
                {(isFormSubmitting || shouldShowSpinner) && (
                    <Loader className="absolute inset-0 z-overlay flex h-full w-full items-center justify-center rounded-sm bg-background-more py-2 opacity-50" />
                )}

                <Button {...props} disabled={isDisabled} hasDisabledLook={hasDisabledLook} type="submit">
                    {children}
                </Button>
            </div>
        );
    },
);

SubmitButton.displayName = 'SubmitButton';

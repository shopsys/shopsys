import { Button, ButtonProps } from './Button';
import { forwardRef } from 'react';
import { useFormContext } from 'react-hook-form';

export const SubmitButton: FC<ButtonProps> = forwardRef(
    (
        { children, hasDisabledLook: isDisabledDefault, disabled, ...props },
        // eslint-disable-next-line @typescript-eslint/no-unused-vars
        _,
    ) => {
        const formProviderMethods = useFormContext();

        // formProviderMethods may be null probably when it is not used in FormProvider context - see https://github.com/react-hook-form/react-hook-form/discussions/3894
        // eslint-disable-next-line @typescript-eslint/no-unnecessary-condition
        const isFormSubmitting = formProviderMethods?.formState.isSubmitting || false;
        const hasDisabledLook = isDisabledDefault || isFormSubmitting;
        const isDisabled = disabled || isFormSubmitting;

        return (
            <Button {...props} disabled={isDisabled} hasDisabledLook={hasDisabledLook} type="submit">
                {children}
            </Button>
        );
    },
);

SubmitButton.displayName = 'SubmitButton';

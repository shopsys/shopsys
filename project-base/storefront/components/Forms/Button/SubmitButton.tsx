import { forwardRef } from 'react';
import { useFormContext } from 'react-hook-form';
import { Button, ButtonProps } from './Button';

export const SubmitButton: FC<ButtonProps> = forwardRef(
    (
        { children, isDisabled: isDisabledDefault, ...props },
        // eslint-disable-next-line @typescript-eslint/no-unused-vars
        _,
    ) => {
        const formProviderMethods = useFormContext();

        // formProviderMethods may be null probably when it is not used in FormProvider context - see https://github.com/react-hook-form/react-hook-form/discussions/3894
        // eslint-disable-next-line @typescript-eslint/no-unnecessary-condition
        const isDisabled = isDisabledDefault || formProviderMethods?.formState.isSubmitting;

        return (
            <Button {...props} type="submit" isDisabled={isDisabled}>
                {children}
            </Button>
        );
    },
);

SubmitButton.displayName = 'SubmitButton';

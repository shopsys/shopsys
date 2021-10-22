import { DeepPartial, UnpackNestedValue, UseFormReturn } from 'react-hook-form';
import { useEffect } from 'react';
import { UseMutationState } from 'urql';

export const useHandleFormSuccessfulSubmit = <T>(
    result: UseMutationState,
    formProviderMethods: UseFormReturn<T>,
    defaultValues: UnpackNestedValue<DeepPartial<T>>,
    onSuccessAction?: (resultData: UseMutationState['data']) => void,
    options?: { blur?: boolean; reset?: boolean },
): void => {
    useEffect(() => {
        if (result.data === undefined || result.error !== undefined) {
            return;
        }

        if (options?.blur && document.activeElement instanceof HTMLElement) {
            document.activeElement.blur();
        }

        if (onSuccessAction !== undefined) {
            onSuccessAction(result.data);
        }
    }, [result.data, result.error]);

    useEffect(() => {
        if (options?.reset && formProviderMethods.formState.isSubmitSuccessful) {
            formProviderMethods.reset(defaultValues);
        }
    }, [formProviderMethods.formState, formProviderMethods.reset]);
};

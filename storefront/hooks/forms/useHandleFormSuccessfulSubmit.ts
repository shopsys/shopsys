import { canUseDom } from 'helpers/misc/canUseDom';
import { useEffect } from 'react';
import { DeepPartial, UnpackNestedValue, UseFormReturn } from 'react-hook-form';
import { UseMutationState } from 'urql';

// TODO: předělat z hooku na normální handler
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

        if (options?.blur && canUseDom() && document.activeElement instanceof HTMLElement) {
            document.activeElement.blur();
        }

        if (onSuccessAction !== undefined) {
            onSuccessAction(result.data);
        }
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [result.data, result.error]);

    useEffect(() => {
        if (options?.reset && formProviderMethods.formState.isSubmitSuccessful && !result.fetching) {
            formProviderMethods.reset(defaultValues, { keepValues: result.error !== undefined, keepErrors: true });
        }
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [formProviderMethods.formState, formProviderMethods.reset, result.fetching, result.error]);
};

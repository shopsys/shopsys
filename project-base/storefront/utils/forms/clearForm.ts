import { DeepPartial, FieldValues, UseFormReturn } from 'react-hook-form';
import { CombinedError } from 'urql';

export const clearForm = <T extends FieldValues>(
    error: CombinedError | undefined,
    formProviderMethods: UseFormReturn<T>,
    defaultValues: DeepPartial<T>,
): void => {
    if (formProviderMethods.formState.isSubmitSuccessful) {
        formProviderMethods.reset(defaultValues, { keepValues: error !== undefined, keepErrors: true });
    }
};

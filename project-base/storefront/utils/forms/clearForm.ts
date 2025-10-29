import { DeepPartial, FieldValues, UseFormReturn } from 'react-hook-form';
import { CombinedError } from 'urql';

export const clearForm = <T extends FieldValues>(
    error: CombinedError | undefined,
    formProviderMethods: UseFormReturn<T>,
    defaultValues: DeepPartial<T>,
): void => {
    if (error === undefined) {
        formProviderMethods.reset(defaultValues, { keepErrors: false });
    }
};

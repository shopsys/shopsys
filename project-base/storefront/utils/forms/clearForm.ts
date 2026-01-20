import { DefaultValues, FieldValues, UseFormReturn } from 'react-hook-form';
import { CombinedError } from 'urql';

export const clearForm = <T extends FieldValues>(
    error: CombinedError | undefined,
    formProviderMethods: UseFormReturn<T>,
    defaultValues: DefaultValues<T>,
): void => {
    if (error === undefined) {
        formProviderMethods.reset(defaultValues, { keepErrors: false });
    }
};

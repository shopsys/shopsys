import { useEffect } from 'react';
import { FieldValues, UseFormReturn } from 'react-hook-form';

export const useScrollToFirstError = <T extends FieldValues>(
    formName: string,
    formProviderMethods: UseFormReturn<T>,
) => {
    useEffect(() => {
        const { isSubmitted, errors } = formProviderMethods.formState;

        if (isSubmitted && Object.keys(errors).length > 0) {
            const firstErrorField = Object.keys(errors)[0];
            const errorElement = document.getElementById(`${formName}-${firstErrorField}`);

            if (errorElement) {
                errorElement.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        }
    }, [formProviderMethods.formState.isSubmitted, formProviderMethods.formState.errors, formName]);
};

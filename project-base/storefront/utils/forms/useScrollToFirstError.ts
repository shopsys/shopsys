import { useEffect } from 'react';
import { FieldValues, UseFormReturn } from 'react-hook-form';

export const useScrollToFirstError = <T extends FieldValues>(
    formName: string,
    formProviderMethods: UseFormReturn<T>,
) => {
    const { submitCount, errors, isSubmitted } = formProviderMethods.formState;

    useEffect(() => {
        if (isSubmitted && Object.keys(errors).length > 0) {
            const firstErrorField = Object.keys(errors)[0];
            const errorElement = document.getElementById(`${formName}-${firstErrorField}`);

            if (errorElement) {
                errorElement.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        }
    }, [submitCount, errors, formName, isSubmitted]);
};

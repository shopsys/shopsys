import { useEffect, useRef } from 'react';
import { FieldValues, UseFormReturn } from 'react-hook-form';

export const useScrollToFirstError = <T extends FieldValues>(
    formName: string,
    formProviderMethods?: UseFormReturn<T>,
) => {
    const { submitCount, errors } = formProviderMethods?.formState ?? {};
    const errorsRef = useRef(errors);
    errorsRef.current = errors;

    useEffect(() => {
        const currentErrors = errorsRef.current;

        if (submitCount && currentErrors && Object.keys(currentErrors).length > 0) {
            const firstErrorField = Object.keys(currentErrors)[0];
            const errorElement = document.getElementById(`${formName}-${firstErrorField}`);

            if (errorElement) {
                errorElement.scrollIntoView({ behavior: 'smooth', block: 'center' });
                errorElement.focus({ preventScroll: true });
            }
        }
    }, [submitCount, formName]);
};

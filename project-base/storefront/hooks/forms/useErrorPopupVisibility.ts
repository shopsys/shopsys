import { Dispatch, SetStateAction, useEffect, useState } from 'react';
import { FieldValues, UseFormReturn } from 'react-hook-form';

export const useErrorPopupVisibility = <T extends FieldValues>(
    formProviderMethods: UseFormReturn<T>,
    overrideVisibility?: boolean,
): [boolean, Dispatch<SetStateAction<boolean>>] => {
    const [isErrorPopupVisible, setErrorPopupVisibility] = useState(false);

    useEffect(() => {
        if (
            formProviderMethods.formState.isSubmitting &&
            (Object.keys(formProviderMethods.formState.errors).length > 0 || overrideVisibility)
        ) {
            setErrorPopupVisibility(true);
        }
    }, [formProviderMethods.formState.isSubmitting, formProviderMethods.formState.errors, overrideVisibility]);

    return [isErrorPopupVisible, setErrorPopupVisibility];
};

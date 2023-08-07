import { Dispatch, SetStateAction, useEffect, useState } from 'react';
import { FieldValues, UseFormReturn } from 'react-hook-form';

export const useErrorPopupVisibility = <T extends FieldValues>(
    formProviderMethods: UseFormReturn<T>,
    overrideVisibility?: boolean,
): [boolean, Dispatch<SetStateAction<boolean>>] => {
    const [isErrorPopupVisible, setErrorPopupVisibility] = useState(false);

    useEffect(() => {
        if (
            formProviderMethods.formState.isSubmitted &&
            (Object.keys(formProviderMethods.formState.errors).length > 0 || overrideVisibility)
        ) {
            setErrorPopupVisibility(true);
        }
    }, [formProviderMethods.formState.isSubmitted, formProviderMethods.formState.errors, overrideVisibility]);

    return [isErrorPopupVisible, setErrorPopupVisibility];
};

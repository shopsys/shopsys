import { Dispatch, SetStateAction, useEffect, useState } from 'react';
import { UseFormReturn } from 'react-hook-form';

export const useHandleErrorPopupVisibility = <T>(
    formProviderMethods: UseFormReturn<T>,
): [boolean, Dispatch<SetStateAction<boolean>>] => {
    const [isErrorPopupVisible, setErrorPopupVisibility] = useState(false);

    useEffect(() => {
        if (
            formProviderMethods.formState.isSubmitting &&
            Object.keys(formProviderMethods.formState.errors).length > 0
        ) {
            setErrorPopupVisibility(true);
        }
    }, [formProviderMethods.formState.isSubmitting]);

    return [isErrorPopupVisible, setErrorPopupVisibility];
};

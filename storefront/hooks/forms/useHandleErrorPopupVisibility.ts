import { Dispatch, SetStateAction, useEffect, useState } from 'react';
import { UseFormReturn } from 'react-hook-form';

// TODO: předělat z hooku na normální handler
export const useHandleErrorPopupVisibility = <T>(
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
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [formProviderMethods.formState.isSubmitting]);

    return [isErrorPopupVisible, setErrorPopupVisibility];
};

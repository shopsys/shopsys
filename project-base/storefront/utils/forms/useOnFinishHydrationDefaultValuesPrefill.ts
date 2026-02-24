import { useEffect, useEffectEvent } from 'react';
import { FieldValues, UseFormReturn } from 'react-hook-form';

export const useOnFinishHydrationDefaultValuesPrefill = <T extends FieldValues>(
    defaultValues: T,
    formProviderMethods: UseFormReturn<T>,
) => {
    const onPrefill = useEffectEvent(() => {
        for (const key in defaultValues) {
            formProviderMethods.setValue(key as any, defaultValues[key]);
        }
    });

    useEffect(() => {
        onPrefill();
    }, []);
};

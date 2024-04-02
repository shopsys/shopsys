import { useEffect } from 'react';
import { FieldValues, UseFormReturn } from 'react-hook-form';
import { isStoreHydrated } from 'utils/store/isStoreHydrated';

export const useOnFinishHydrationDefaultValuesPrefill = <T extends FieldValues>(
    defaultValues: T,
    formProviderMethods: UseFormReturn<T>,
) => {
    const hasHydratedOnClient = isStoreHydrated();

    useEffect(() => {
        if (hasHydratedOnClient) {
            for (const key in defaultValues) {
                formProviderMethods.setValue(key as any, defaultValues[key]);
            }
        }
    }, [hasHydratedOnClient]);
};

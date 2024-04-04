import { useMemo } from 'react';
import { UseFormReturn } from 'react-hook-form';
import { usePersistStore } from 'store/usePersistStore';
import { UserConsentFormType } from 'types/form';
import { useOnFinishHydrationDefaultValuesPrefill } from 'utils/forms/useOnFinishHydrationDefaultValuesPrefill';
import { useShopsysForm } from 'utils/forms/useShopsysForm';

export const useUserConsentForm = (): [UseFormReturn<UserConsentFormType>, UserConsentFormType] => {
    const userConsent = usePersistStore((store) => store.userConsent);

    const defaultValues = userConsent ?? {
        statistics: false,
        marketing: false,
        preferences: false,
    };
    const formProviderMethods = useShopsysForm(undefined, defaultValues);

    useOnFinishHydrationDefaultValuesPrefill(defaultValues, formProviderMethods);

    return [formProviderMethods, defaultValues];
};

type UserConsentFormMeta = {
    formName: string;
    fields: {
        [key in keyof UserConsentFormType]: {
            name: key;
        };
    };
};

export const useUserConsentFormMeta = (): UserConsentFormMeta => {
    const formMeta = useMemo(
        () => ({
            formName: 'user-consent-form',
            fields: {
                marketing: {
                    name: 'marketing' as const,
                },
                preferences: {
                    name: 'preferences' as const,
                },
                statistics: {
                    name: 'statistics' as const,
                },
            },
        }),
        [],
    );

    return formMeta;
};

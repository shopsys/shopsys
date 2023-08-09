import { useShopsysForm } from 'hooks/forms/useShopsysForm';
import { useMemo } from 'react';
import { UseFormReturn } from 'react-hook-form';
import { usePersistStore } from 'store/usePersistStore';
import { UserConsentFormType } from 'types/form';

export const useUserConsentForm = (): [UseFormReturn<UserConsentFormType>, UserConsentFormType] => {
    const userConsent = usePersistStore((store) => store.userConsent);

    const defaultValues = userConsent ?? {
        statistics: false,
        marketing: false,
        preferences: false,
    };

    return [useShopsysForm(undefined, defaultValues), defaultValues];
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

import { UseFormReturn } from 'react-hook-form';
import { usePersistStore } from 'store/usePersistStore';
import { UserConsentFormType } from 'types/form';
import { useFormWrapper } from 'utils/forms/useFormWrapper';
import { useOnFinishHydrationDefaultValuesPrefill } from 'utils/forms/useOnFinishHydrationDefaultValuesPrefill';

export const useUserConsentForm = (): [UseFormReturn<UserConsentFormType>, UserConsentFormType] => {
    const userConsent = usePersistStore((store) => store.userConsent);

    const defaultValues = userConsent ?? {
        statistics: false,
        marketing: false,
        preferences: false,
    };
    const formProviderMethods = useFormWrapper(undefined, defaultValues);

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
    return {
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
    };
};

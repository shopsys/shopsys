import { getUserConsentCookie } from 'helpers/cookies/getUserConsentCookie';
import { useShopsysForm } from 'hooks/forms/UseShopsysForm';
import { UseFormReturn } from 'react-hook-form';
import { UserConsentFormType } from 'types/form';

export const useUserConsentForm = (): [UseFormReturn<UserConsentFormType>, UserConsentFormType] => {
    const userContentCookie = getUserConsentCookie();

    const defaultValues = userContentCookie ?? {
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
    const formMeta: UserConsentFormMeta = {
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

    return formMeta;
};

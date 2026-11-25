import { useEffect } from 'react';
import { UseFormReturn } from 'react-hook-form';
import { usePersistStoreApi } from 'store/usePersistStore';
import { UserConsentFormType } from 'types/form';
import { FormMeta } from 'types/formMeta';
import { createFields } from 'utils/forms/createFields';
import { useFormWrapper } from 'utils/forms/useFormWrapper';
import useTranslation from 'utils/i18n/useTranslationWrapper';

const emptyConsent: UserConsentFormType = {
    statistics: false,
    marketing: false,
    preferences: false,
};

export const useUserConsentForm = (): UseFormReturn<UserConsentFormType> => {
    const store = usePersistStoreApi();
    const formProviderMethods = useFormWrapper(undefined, emptyConsent);
    const { reset } = formProviderMethods;

    useEffect(() => {
        const { userConsent } = store.getState();
        if (userConsent) {
            reset(userConsent);
        }
    }, [store, reset]);

    return formProviderMethods;
};

export const useUserConsentFormMeta = (): FormMeta<UserConsentFormType> => {
    const { t } = useTranslation();

    return {
        formName: 'user-consent-form',
        messages: {},
        fields: createFields<UserConsentFormType>({
            marketing: t('Marketing'),
            preferences: t('Preferences'),
            statistics: t('Statistics'),
        }),
    };
};

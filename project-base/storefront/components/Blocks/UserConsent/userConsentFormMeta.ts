import { UseFormReturn } from 'react-hook-form';
import { usePersistStore } from 'store/usePersistStore';
import { UserConsentFormType } from 'types/form';
import { FormMeta } from 'types/formMeta';
import { createFields } from 'utils/forms/createFields';
import { useFormWrapper } from 'utils/forms/useFormWrapper';
import { useOnFinishHydrationDefaultValuesPrefill } from 'utils/forms/useOnFinishHydrationDefaultValuesPrefill';
import useTranslation from 'utils/i18n/useTranslationWrapper';

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

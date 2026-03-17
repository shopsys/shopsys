import { yupResolver } from '@hookform/resolvers/yup';
import { validateEmail, validatePassword } from 'components/Forms/validationRules';
import { UseFormReturn } from 'react-hook-form';
import { LoginFormType } from 'types/form';
import { FormMeta } from 'types/formMeta';
import { createFields } from 'utils/forms/createFields';
import { useFormWrapper } from 'utils/forms/useFormWrapper';
import { useOnFinishHydrationDefaultValuesPrefill } from 'utils/forms/useOnFinishHydrationDefaultValuesPrefill';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import * as Yup from 'yup';

export const useLoginForm = (defaultEmail?: string): [UseFormReturn<LoginFormType>, LoginFormType] => {
    const { t } = useTranslation();

    const resolver = yupResolver(
        Yup.object().shape<Record<keyof LoginFormType, any>>({
            email: validateEmail(t),
            password: validatePassword(t),
        }),
    );

    const defaultValues = {
        email: defaultEmail ?? '',
        password: '',
    };

    const formProviderMethods = useFormWrapper(resolver, defaultValues);
    useOnFinishHydrationDefaultValuesPrefill(defaultValues, formProviderMethods);

    return [formProviderMethods, defaultValues];
};

export const useLoginFormMeta = (): FormMeta<LoginFormType> => {
    const { t } = useTranslation();

    return {
        formName: 'login-form',
        messages: {},
        fields: createFields<LoginFormType>({
            email: t('Your email'),
            password: t('Password'),
        }),
    };
};

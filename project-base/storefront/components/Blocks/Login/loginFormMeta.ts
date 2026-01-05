import { yupResolver } from '@hookform/resolvers/yup';
import { validateEmail, validatePassword } from 'components/Forms/validationRules';
import { useMemo } from 'react';
import { UseFormReturn } from 'react-hook-form';
import { LoginFormType } from 'types/form';
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

type LoginFormMetaType = {
    formName: string;
    fields: {
        [key in keyof LoginFormType]: {
            name: key;
            label: string;
            errorMessage: string | undefined;
        };
    };
};

export const useLoginFormMeta = (formProviderMethods: UseFormReturn<LoginFormType>): LoginFormMetaType => {
    const { t } = useTranslation();
    const errors = formProviderMethods.formState.errors;

    const formMeta = useMemo(
        () => ({
            formName: 'login-form',
            fields: {
                email: {
                    name: 'email' as const,
                    label: t('Your email'),
                    errorMessage: errors.email?.message,
                },
                password: {
                    name: 'password' as const,
                    label: t('Password'),
                    errorMessage: errors.password?.message,
                },
            },
        }),
        [errors.email?.message, errors.password?.message, t],
    );

    return formMeta;
};

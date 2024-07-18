import { yupResolver } from '@hookform/resolvers/yup';
import useTranslation from 'next-translate/useTranslation';
import { useMemo } from 'react';
import { UseFormReturn } from 'react-hook-form';
import { LoginFormType } from 'types/form';
import { useOnFinishHydrationDefaultValuesPrefill } from 'utils/forms/useOnFinishHydrationDefaultValuesPrefill';
import { useShopsysForm } from 'utils/forms/useShopsysForm';
import * as Yup from 'yup';

export const useLoginForm = (defaultEmail?: string): [UseFormReturn<LoginFormType>, LoginFormType] => {
    const { t } = useTranslation();

    const resolver = yupResolver(
        Yup.object().shape<Record<keyof LoginFormType, any>>({
            email: Yup.string().required(t('This field is required')).email(t('This value is not a valid email')),
            password: Yup.string().required(t('This field is required')),
        }),
    );
    const defaultValues = {
        email: defaultEmail ?? '',
        password: '',
    };
    const formProviderMethods = useShopsysForm(resolver, defaultValues);
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

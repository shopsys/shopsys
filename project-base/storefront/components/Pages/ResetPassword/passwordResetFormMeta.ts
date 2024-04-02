import { yupResolver } from '@hookform/resolvers/yup';
import useTranslation from 'next-translate/useTranslation';
import { useMemo } from 'react';
import { UseFormReturn } from 'react-hook-form';
import { PasswordResetFormType } from 'types/form';
import { useShopsysForm } from 'utils/forms/useShopsysForm';
import * as Yup from 'yup';

export const usePasswordResetForm = (): [UseFormReturn<PasswordResetFormType>, PasswordResetFormType] => {
    const { t } = useTranslation();
    const resolver = yupResolver(
        Yup.object().shape<Record<keyof PasswordResetFormType, any>>({
            email: Yup.string().required(t('This field is required')).email(t('This value is not a valid email')),
        }),
    );
    const defaultValues = { email: '' };

    return [useShopsysForm(resolver, defaultValues), defaultValues];
};

type PasswordResetFormMetaType = {
    formName: string;
    messages: {
        error: string;
        success: string;
    };
    fields: {
        [key in keyof PasswordResetFormType]: {
            name: key;
            label: string;
            errorMessage: string | undefined;
        };
    };
};

export const usePasswordResetFormMeta = (
    formProviderMethods: UseFormReturn<PasswordResetFormType>,
): PasswordResetFormMetaType => {
    const { t } = useTranslation();

    const formMeta = useMemo(
        () => ({
            formName: 'password-reset-form',
            messages: {
                error: t('Could not reset password'),
                success: t('We sent an email with further steps to your address'),
            },
            fields: {
                email: {
                    name: 'email' as const,
                    label: t('Your email'),
                    errorMessage: formProviderMethods.formState.errors.email?.message,
                },
            },
        }),
        [formProviderMethods.formState.errors.email?.message, t],
    );

    return formMeta;
};

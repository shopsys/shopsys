import { yupResolver } from '@hookform/resolvers/yup';
import { validateEmail } from 'components/Forms/validationRules';
import useTranslation from 'next-translate/useTranslation';
import { useMemo } from 'react';
import { UseFormReturn } from 'react-hook-form';
import { ResetPasswordFormType } from 'types/form';
import { useShopsysForm } from 'utils/forms/useShopsysForm';
import * as Yup from 'yup';

export const useResetPasswordForm = (): [UseFormReturn<ResetPasswordFormType>, ResetPasswordFormType] => {
    const { t } = useTranslation();
    const resolver = yupResolver(
        Yup.object().shape<Record<keyof ResetPasswordFormType, any>>({
            email: validateEmail(t),
        }),
    );
    const defaultValues = { email: '' };

    return [useShopsysForm(resolver, defaultValues), defaultValues];
};

type ResetPasswordFormMetaType = {
    formName: string;
    messages: {
        error: string;
        success: string;
    };
    fields: {
        [key in keyof ResetPasswordFormType]: {
            name: key;
            label: string;
            errorMessage: string | undefined;
        };
    };
};

export const useResetPasswordFormMeta = (
    formProviderMethods: UseFormReturn<ResetPasswordFormType>,
): ResetPasswordFormMetaType => {
    const { t } = useTranslation();
    const errors = formProviderMethods.formState.errors;

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
                    errorMessage: errors.email?.message,
                },
            },
        }),
        [errors.email?.message, t],
    );

    return formMeta;
};

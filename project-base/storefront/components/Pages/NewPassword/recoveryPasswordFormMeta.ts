import { yupResolver } from '@hookform/resolvers/yup';
import { useShopsysForm } from 'hooks/forms/useShopsysForm';
import useTranslation from 'next-translate/useTranslation';
import { useMemo } from 'react';
import { UseFormReturn } from 'react-hook-form';
import { NewPasswordFormType } from 'types/form';
import * as Yup from 'yup';

export const useRecoveryPasswordForm = (): [UseFormReturn<NewPasswordFormType>, NewPasswordFormType] => {
    const { t } = useTranslation();
    const resolver = yupResolver(
        Yup.object().shape({
            newPassword: Yup.string()
                .required(t('Fill first password'))
                .min(
                    6,
                    t('Password must be at least {{ count }} characters long', {
                        count: 6,
                    }),
                ),
            newPasswordAgain: Yup.string().when('newPassword', {
                is: (newPassword: string) => newPassword.length > 0,
                then: Yup.string()
                    .required(t('Fill second password'))
                    .oneOf([Yup.ref('newPassword'), null], t('Passwords must match'))
                    .min(
                        6,
                        t('Password must be at least {{ count }} characters long', {
                            count: 6,
                        }),
                    ),
                otherwise: Yup.string(),
            }),
        }),
    );
    const defaultValues = {
        newPassword: '',
        newPasswordAgain: '',
    };

    return [useShopsysForm(resolver, defaultValues), defaultValues];
};

type NewPasswordFormMetaType = {
    formName: string;
    messages: {
        error: string;
        success: string;
    };
    fields: {
        [key in keyof NewPasswordFormType]: {
            name: key;
            label: string;
            errorMessage: string | undefined;
        };
    };
};

export const useRecoveryPasswordFormMeta = (
    formProviderMethods: UseFormReturn<NewPasswordFormType>,
): NewPasswordFormMetaType => {
    const { t } = useTranslation();

    const formMeta = useMemo(
        () => ({
            formName: 'new-password-form',
            messages: {
                error: t('Error occured while changing your password'),
                success: t('Your password has been changed and you are now logged in'),
            },
            fields: {
                newPassword: {
                    name: 'newPassword' as const,
                    label: t('New password'),
                    errorMessage: formProviderMethods.formState.errors.newPassword?.message,
                },
                newPasswordAgain: {
                    name: 'newPasswordAgain' as const,
                    label: t('New password again'),
                    errorMessage: formProviderMethods.formState.errors.newPasswordAgain?.message,
                },
            },
        }),
        [
            formProviderMethods.formState.errors.newPassword?.message,
            formProviderMethods.formState.errors.newPasswordAgain?.message,
            t,
        ],
    );

    return formMeta;
};

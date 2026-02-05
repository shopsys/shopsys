import { yupResolver } from '@hookform/resolvers/yup';
import { validateNewPassword, validateNewPasswordConfirm } from 'components/Forms/validationRules';
import { UseFormReturn } from 'react-hook-form';
import { NewPasswordFormType } from 'types/form';
import { useFormWrapper } from 'utils/forms/useFormWrapper';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import * as Yup from 'yup';

export const useRecoveryPasswordForm = (): [UseFormReturn<NewPasswordFormType>, NewPasswordFormType] => {
    const { t } = useTranslation();
    const resolver = yupResolver(
        Yup.object().shape<Record<keyof NewPasswordFormType, any>>({
            newPassword: validateNewPassword(t),
            newPasswordConfirm: Yup.string().when('newPassword', {
                is: (newPassword: string) => newPassword.length > 0,
                then: () => validateNewPasswordConfirm(t),
                otherwise: (schema) => schema,
            }),
        }),
    );
    const defaultValues = {
        newPassword: '',
        newPasswordConfirm: '',
    };

    return [useFormWrapper(resolver, defaultValues), defaultValues];
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
    const errors = formProviderMethods.formState.errors;

    return {
        formName: 'new-password-form',
        messages: {
            error: t('An error occurred while changing your password'),
            success: t('Your password has been changed successfully'),
        },
        fields: {
            newPassword: {
                name: 'newPassword' as const,
                label: t('New password'),
                errorMessage: errors.newPassword?.message,
            },
            newPasswordConfirm: {
                name: 'newPasswordConfirm' as const,
                label: t('New password again'),
                errorMessage: errors.newPasswordConfirm?.message,
            },
        },
    };
};

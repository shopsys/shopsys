import { validateNewPassword, validateNewPasswordConfirm, validateOldPassword } from 'components/Forms/validationRules';
import { UseFormReturn } from 'react-hook-form';
import { ChangePasswordFormType } from 'types/form';
import { FormMeta } from 'types/formMeta';
import { createFields } from 'utils/forms/createFields';
import { useFormWrapper } from 'utils/forms/useFormWrapper';
import { yupResolver } from 'utils/forms/yupResolver';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import * as Yup from 'yup';

export const useChangePasswordForm = (
    defaultValues: ChangePasswordFormType,
): [UseFormReturn<ChangePasswordFormType>, ChangePasswordFormType] => {
    const { t } = useTranslation();

    const resolver = yupResolver<ChangePasswordFormType>(
        Yup.object().shape<Record<keyof ChangePasswordFormType, any>>({
            oldPassword: validateOldPassword(t),
            newPassword: validateNewPassword(t),
            newPasswordConfirm: validateNewPasswordConfirm(t),
        }),
    );

    return [useFormWrapper(resolver, defaultValues), defaultValues];
};

export const useChangePasswordFormMeta = (): FormMeta<ChangePasswordFormType, { error: string; success: string }> => {
    const { t } = useTranslation();
    return {
        formName: 'customer-change-password-form',
        messages: {
            error: t('An error occurred while changing your password'),
            success: t('Your password has been changed successfully'),
        },
        fields: createFields<ChangePasswordFormType>({
            oldPassword: t('Current password'),
            newPassword: t('New password'),
            newPasswordConfirm: t('New password again'),
        }),
    };
};

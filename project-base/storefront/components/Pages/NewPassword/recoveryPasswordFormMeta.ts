import { validateNewPassword, validateNewPasswordConfirm } from 'components/Forms/validationRules';
import { UseFormReturn } from 'react-hook-form';
import { NewPasswordFormType } from 'types/form';
import { FormMeta } from 'types/formMeta';
import { createFields } from 'utils/forms/createFields';
import { useFormWrapper } from 'utils/forms/useFormWrapper';
import { yupResolver } from 'utils/forms/yupResolver';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import * as Yup from 'yup';

export const useRecoveryPasswordForm = (): [UseFormReturn<NewPasswordFormType>, NewPasswordFormType] => {
    const { t } = useTranslation();
    const resolver = yupResolver<NewPasswordFormType>(
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

export const useRecoveryPasswordFormMeta = (): FormMeta<NewPasswordFormType, { success: string }> => {
    const { t } = useTranslation();
    return {
        formName: 'new-password-form',
        messages: {
            success: t('Your password has been changed successfully'),
        },
        fields: createFields<NewPasswordFormType>({
            newPassword: t('New password'),
            newPasswordConfirm: t('New password again'),
        }),
    };
};

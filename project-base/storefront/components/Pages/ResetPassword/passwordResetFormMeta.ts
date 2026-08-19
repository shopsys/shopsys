import { validateEmail } from 'components/Forms/validationRules';
import { UseFormReturn } from 'react-hook-form';
import { PasswordResetFormType } from 'types/form';
import { FormMeta } from 'types/formMeta';
import { createFields } from 'utils/forms/createFields';
import { useFormWrapper } from 'utils/forms/useFormWrapper';
import { yupResolver } from 'utils/forms/yupResolver';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import * as Yup from 'yup';

export const usePasswordResetForm = (): [UseFormReturn<PasswordResetFormType>, PasswordResetFormType] => {
    const { t } = useTranslation();
    const resolver = yupResolver<PasswordResetFormType>(
        Yup.object().shape<Record<keyof PasswordResetFormType, any>>({
            email: validateEmail(t),
        }),
    );
    const defaultValues = { email: '' };

    return [useFormWrapper(resolver, defaultValues), defaultValues];
};

export const usePasswordResetFormMeta = (): FormMeta<PasswordResetFormType, { success: string }> => {
    const { t } = useTranslation();
    return {
        formName: 'password-reset-form',
        messages: {
            success: t(
                'We have sent password reset instructions to your email address. Please check your inbox and follow the link to create a new password.',
            ),
        },
        fields: createFields<PasswordResetFormType>({
            email: t('Your email'),
        }),
    };
};

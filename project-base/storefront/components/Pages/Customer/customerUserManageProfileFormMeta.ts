import {
    validateEmail,
    validateFirstName,
    validateLastName,
    validateRoleGroup,
    validateTelephonePrefix,
    validateTelephoneRequired,
} from 'components/Forms/validationRules';
import { UseFormReturn } from 'react-hook-form';
import { CustomerUserManageProfileFormType } from 'types/form';
import { FormMeta } from 'types/formMeta';
import { createFields } from 'utils/forms/createFields';
import { useFormWrapper } from 'utils/forms/useFormWrapper';
import { yupResolver } from 'utils/forms/yupResolver';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import * as Yup from 'yup';

export const useCustomerUserManageProfileForm = (
    defaultValues: CustomerUserManageProfileFormType,
): [UseFormReturn<CustomerUserManageProfileFormType>, CustomerUserManageProfileFormType] => {
    const { t } = useTranslation();

    const resolver = yupResolver<CustomerUserManageProfileFormType>(
        Yup.object().shape<Record<keyof CustomerUserManageProfileFormType, any>>({
            email: validateEmail(t),
            telephonePrefix: validateTelephonePrefix(t),
            telephonePrefixCountryCode: Yup.string(),
            telephone: validateTelephoneRequired(t),
            firstName: validateFirstName(t),
            lastName: validateLastName(t),
            roleGroup: validateRoleGroup(t),
        }),
    );

    return [useFormWrapper(resolver, defaultValues), defaultValues];
};

export const useCustomerUserManageProfileFormMeta = (
    mode: 'add' | 'edit',
): FormMeta<CustomerUserManageProfileFormType, { error: string; success: string }> => {
    const { t } = useTranslation();
    return {
        formName: 'customer-user-manage-profile-form',
        messages: {
            error:
                mode === 'edit'
                    ? t('An error occurred while saving user profile')
                    : t('An error occurred while adding user'),
            success:
                mode === 'edit'
                    ? t('User profile has been changed successfully')
                    : t('User has been added successfully'),
        },
        fields: createFields<CustomerUserManageProfileFormType>({
            email: t('User email'),
            telephonePrefix: t('Phone prefix'),
            telephonePrefixCountryCode: '',
            telephone: t('Phone'),
            firstName: t('First name'),
            lastName: t('Last name'),
            roleGroup: t('Role group'),
        }),
    };
};

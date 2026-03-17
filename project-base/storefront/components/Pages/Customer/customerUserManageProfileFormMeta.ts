import { yupResolver } from '@hookform/resolvers/yup';
import {
    validateEmail,
    validateFirstName,
    validateLastName,
    validateRoleGroup,
    validateTelephoneRequired,
} from 'components/Forms/validationRules';
import { UseFormReturn } from 'react-hook-form';
import { CustomerUserManageProfileFormType } from 'types/form';
import { FormMeta } from 'types/formMeta';
import { createFields } from 'utils/forms/createFields';
import { useFormWrapper } from 'utils/forms/useFormWrapper';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import * as Yup from 'yup';

export const useCustomerUserManageProfileForm = (
    defaultValues: CustomerUserManageProfileFormType,
): [UseFormReturn<CustomerUserManageProfileFormType>, CustomerUserManageProfileFormType] => {
    const { t } = useTranslation();

    const resolver = yupResolver(
        Yup.object().shape<Record<keyof CustomerUserManageProfileFormType, any>>({
            email: validateEmail(t),
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
            telephone: t('Phone'),
            firstName: t('First name'),
            lastName: t('Last name'),
            roleGroup: t('Role group'),
        }),
    };
};

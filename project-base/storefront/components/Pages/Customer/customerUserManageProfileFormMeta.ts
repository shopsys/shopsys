import { yupResolver } from '@hookform/resolvers/yup';
import {
    validateEmail,
    validateFirstName,
    validateLastName,
    validateRoleGroup,
    validateTelephoneRequired,
} from 'components/Forms/validationRules';
import { FieldError, UseFormReturn } from 'react-hook-form';
import { CustomerUserManageProfileFormType } from 'types/form';
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

type CustomerUserManageProfileFormMetaType = {
    formName: string;
    messages: {
        error: string;
        success: string;
    };
    fields: {
        [key in keyof CustomerUserManageProfileFormType]: {
            name: key;
            label: string;
            errorMessage?: string;
        };
    };
};

export const useCustomerUserManageProfileFormMeta = (
    formProviderMethods: UseFormReturn<CustomerUserManageProfileFormType>,
    mode: 'add' | 'edit',
): CustomerUserManageProfileFormMetaType => {
    const { t } = useTranslation();
    const errors = formProviderMethods.formState.errors;

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
        fields: {
            email: {
                name: 'email' as const,
                label: t('User email'),
                errorMessage: errors.email?.message,
            },
            telephone: {
                name: 'telephone' as const,
                label: t('Phone'),
                errorMessage: errors.telephone?.message,
            },
            firstName: {
                name: 'firstName' as const,
                label: t('First name'),
                errorMessage: errors.firstName?.message,
            },
            lastName: {
                name: 'lastName' as const,
                label: t('Last name'),
                errorMessage: errors.lastName?.message,
            },
            roleGroup: {
                name: 'roleGroup' as const,
                label: t('Role group'),
                errorMessage: (errors.roleGroup as FieldError | undefined)?.message,
            },
        },
    };
};

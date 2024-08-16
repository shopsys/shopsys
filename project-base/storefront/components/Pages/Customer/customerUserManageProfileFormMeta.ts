import { yupResolver } from '@hookform/resolvers/yup';
import {
    validateEmail,
    validateFirstName,
    validateLastName,
    validateRoleGroup,
    validateTelephoneRequired,
} from 'components/Forms/validationRules';
import useTranslation from 'next-translate/useTranslation';
import { useMemo } from 'react';
import { FieldError, UseFormReturn } from 'react-hook-form';
import { CustomerUserManageProfileFormType } from 'types/form';
import { useShopsysForm } from 'utils/forms/useShopsysForm';
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

    return [useShopsysForm(resolver, defaultValues), defaultValues];
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

    const formMeta = useMemo(
        () => ({
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
        }),
        [
            errors.email?.message,
            errors.telephone?.message,
            errors.firstName?.message,
            errors.lastName?.message,
            errors.roleGroup,
            t,
        ],
    );
    return formMeta;
};

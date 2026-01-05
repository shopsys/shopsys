import { yupResolver } from '@hookform/resolvers/yup';
import {
    validateCity,
    validateCompanyNameRequired,
    validateCompanyNumber,
    validateCompanyTaxNumber,
    validateCountry,
    validateEmail,
    validateFirstName,
    validateLastName,
    validatePostcode,
    validateStreet,
    validateTelephoneRequired,
} from 'components/Forms/validationRules';
import { useMemo } from 'react';
import { FieldError, UseFormReturn } from 'react-hook-form';
import { CustomerChangeProfileFormType } from 'types/form';
import { useFormWrapper } from 'utils/forms/useFormWrapper';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import * as Yup from 'yup';

export const useCustomerChangeProfileForm = (
    defaultValues: CustomerChangeProfileFormType,
): [UseFormReturn<CustomerChangeProfileFormType>, CustomerChangeProfileFormType] => {
    const { t } = useTranslation();

    const resolver = yupResolver(
        Yup.object().shape<Record<keyof CustomerChangeProfileFormType, any>>({
            email: validateEmail(t),
            telephone: validateTelephoneRequired(t),
            firstName: validateFirstName(t),
            lastName: validateLastName(t),
            street: validateStreet(t),
            city: validateCity(t),
            postcode: validatePostcode(t),
            country: validateCountry(t),
            companyName: Yup.string().when('companyCustomer', {
                is: true,
                then: () => validateCompanyNameRequired(t),
                otherwise: (schema) => schema,
            }),
            companyNumber: Yup.string().when('companyCustomer', {
                is: true,
                then: () => validateCompanyNumber(t),
                otherwise: (schema) => schema,
            }),
            companyTaxNumber: Yup.string().when('companyCustomer', {
                is: true,
                then: () => validateCompanyTaxNumber(t),
                otherwise: (schema) => schema,
            }),
            newsletterSubscription: Yup.boolean(),
            companyCustomer: Yup.boolean(),
        }),
    );

    return [useFormWrapper(resolver, defaultValues), defaultValues];
};

type CustomerChangeProfileFormMetaType = {
    formName: string;
    messages: {
        error: string;
        success: string;
    };
    fields: {
        [key in keyof CustomerChangeProfileFormType]: {
            name: key;
            label: string;
            errorMessage?: string;
        };
    };
};

export const useCustomerChangeProfileFormMeta = (
    formProviderMethods: UseFormReturn<CustomerChangeProfileFormType>,
): CustomerChangeProfileFormMetaType => {
    const { t } = useTranslation();
    const companyCustomer = formProviderMethods.formState.dirtyFields.companyCustomer;
    const errors = formProviderMethods.formState.errors;

    const formMeta = useMemo(
        () => ({
            formName: 'customer-change-profile-form',
            messages: {
                error: t('An error occurred while saving your profile'),
                success: t('Your profile has been changed successfully'),
            },
            fields: {
                companyCustomer: {
                    name: 'companyCustomer' as const,
                    label: '',
                },
                email: {
                    name: 'email' as const,
                    label: t('Your email'),
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
                companyName: {
                    name: 'companyName' as const,
                    label: t('Company name'),
                    errorMessage: companyCustomer ? errors.companyName?.message : undefined,
                },
                companyNumber: {
                    name: 'companyNumber' as const,
                    label: t('Company number'),
                    errorMessage: companyCustomer ? errors.companyNumber?.message : undefined,
                },
                companyTaxNumber: {
                    name: 'companyTaxNumber' as const,
                    label: t('Tax number'),
                    errorMessage: companyCustomer ? errors.companyTaxNumber?.message : undefined,
                },
                street: {
                    name: 'street' as const,
                    label: t('Street and house no.'),
                    errorMessage: errors.street?.message,
                },
                city: {
                    name: 'city' as const,
                    label: t('City'),
                    errorMessage: errors.city?.message,
                },
                postcode: {
                    name: 'postcode' as const,
                    label: t('Postcode'),
                    errorMessage: errors.postcode?.message,
                },
                country: {
                    name: 'country' as const,
                    label: t('Country'),
                    errorMessage: (errors.country as FieldError | undefined)?.message,
                },
                newsletterSubscription: {
                    name: 'newsletterSubscription' as const,
                    label: t('I agree to receive the newsletter'),
                    errorMessage: errors.newsletterSubscription?.message,
                },
            },
        }),
        [
            errors.email?.message,
            errors.telephone?.message,
            errors.firstName?.message,
            errors.lastName?.message,
            errors.companyName?.message,
            errors.companyNumber?.message,
            errors.companyTaxNumber?.message,
            errors.street?.message,
            errors.city?.message,
            errors.postcode?.message,
            errors.country,
            errors.newsletterSubscription?.message,
            companyCustomer,
            t,
        ],
    );
    return formMeta;
};

import { yupResolver } from '@hookform/resolvers/yup';
import {
    validateCity,
    validateCompanyNameRequired,
    validateCompanyNumber,
    validateCompanyTaxNumber,
    validateCountry,
    validateEmail,
    validateFirstName,
    validateFirstPassword,
    validateLastName,
    validatePostcode,
    validateSecondPassword,
    validateStreet,
    validateTelephoneRequired,
} from 'components/Forms/validationRules';
import { useShopsysForm } from 'hooks/forms/useShopsysForm';
import { useTypedTranslationFunction } from 'hooks/typescript/useTypedTranslationFunction';
import { useMemo } from 'react';
import { FieldError, UseFormReturn } from 'react-hook-form';
import { CustomerChangeProfileFormType } from 'types/form';
import * as Yup from 'yup';

export const useCustomerChangeProfileForm = (
    defaultValues: CustomerChangeProfileFormType,
): [UseFormReturn<CustomerChangeProfileFormType>, CustomerChangeProfileFormType] => {
    const t = useTypedTranslationFunction();

    const resolver = yupResolver(
        Yup.object().shape({
            email: validateEmail(t),
            passwordFirst: Yup.string().when('passwordOld', {
                is: (passwordOld: string) => passwordOld.length > 0,
                then: validateFirstPassword(t),
                otherwise: Yup.string(),
            }),
            passwordSecond: Yup.string().when('passwordFirst', {
                is: (passwordFirst: string) => passwordFirst.length > 0,
                then: validateSecondPassword(t),
                otherwise: Yup.string(),
            }),
            telephone: validateTelephoneRequired(t),
            firstName: validateFirstName(t),
            lastName: validateLastName(t),
            street: validateStreet(t),
            city: validateCity(t),
            postcode: validatePostcode(t),
            country: validateCountry(t),
            companyName: Yup.string().when('customer', {
                is: (customer: string) => customer === 'companyCustomer',
                then: validateCompanyNameRequired(t),
                otherwise: Yup.string(),
            }),
            companyNumber: Yup.string().when('customer', {
                is: (customer: string) => customer === 'companyCustomer',
                then: validateCompanyNumber(t),
                otherwise: Yup.string(),
            }),
            companyTaxNumber: Yup.string().when('customer', {
                is: (customer: string) => customer === 'companyCustomer',
                then: validateCompanyTaxNumber(t),
                otherwise: Yup.string(),
            }),
        }),
    );

    return [useShopsysForm(resolver, defaultValues), defaultValues];
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
    const t = useTypedTranslationFunction();
    const companyCustomer = formProviderMethods.formState.dirtyFields.companyCustomer;
    const errors = formProviderMethods.formState.errors;

    const formMeta = useMemo(
        () => ({
            formName: 'customer-change-profile-form',
            messages: {
                error: t('Error occured while saving your profile'),
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
                passwordOld: {
                    name: 'passwordOld' as const,
                    label: t('Current password'),
                    errorMessage: errors.passwordOld?.message,
                },
                passwordFirst: {
                    name: 'passwordFirst' as const,
                    label: t('New password'),
                    errorMessage: errors.passwordFirst?.message,
                },
                passwordSecond: {
                    name: 'passwordSecond' as const,
                    label: t('New password again'),
                    errorMessage: errors.passwordSecond?.message,
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
                    label: t('Street and house number'),
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
            errors.passwordOld?.message,
            errors.passwordFirst?.message,
            errors.passwordSecond?.message,
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

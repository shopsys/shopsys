import * as Yup from 'yup';
import { FieldError, UseFormReturn } from 'react-hook-form';
import { CustomerChangeProfileFormType } from 'types/form';
import { useShopsysForm } from 'hooks/forms/UseShopsysForm';
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';
import { yupResolver } from '@hookform/resolvers/yup';

export const useCustomerChangeProfileForm = (
    defaultValues: CustomerChangeProfileFormType,
): [UseFormReturn<CustomerChangeProfileFormType>, CustomerChangeProfileFormType] => {
    const t = useTypedTranslationFunction();

    const resolver = yupResolver(
        Yup.object().shape({
            email: Yup.string().required(t('Please enter email')).email(t('This value is not a valid email')).min(5),
            passwordFirst: Yup.string().when('passwordOld', {
                is: (passwordOld: string) => passwordOld.length > 0,
                then: Yup.string().min(
                    6,
                    t('Password must be at least {{ count }} characters long', {
                        postProcess: 'interval',
                        count: 6,
                    }),
                ),
                otherwise: Yup.string(),
            }),
            passwordSecond: Yup.string().when('passwordFirst', {
                is: (passwordFirst: string) => passwordFirst.length > 0,
                then: Yup.string()
                    .required(t('Fill first password'))
                    .oneOf([Yup.ref('passwordFirst'), null], t('Passwords must match'))
                    .min(
                        6,
                        t('Password must be at least {{ count }} characters long', {
                            postProcess: 'interval',
                            count: 6,
                        }),
                    ),
                otherwise: Yup.string(),
            }),
            telephone: Yup.string()
                .required(t('Please enter phone number'))
                .matches(/^[0-9+]*$/, t('Please enter only numbers and the + character'))
                .test(
                    'more-than-8',
                    t('Telephone number cannot be shorter than 9 characters'),
                    (value) => value !== undefined && value.length >= 9,
                ),
            firstName: Yup.string().required(t('Please enter first name')),
            lastName: Yup.string().required(t('Please enter last name')),
            street: Yup.string()
                .required(t('Please enter street'))
                .matches(/\D/, t('The street must contain a letter'))
                .matches(/\d/, t('The street must contain a number')),
            city: Yup.string().required(t('Please enter city')),
            postcode: Yup.string()
                .required(t('Please enter zip code'))
                .test(
                    'less-than-or-equals-5',
                    t('Zip code cannot be longer than 5 characters'),
                    (value) => value !== undefined && value.length <= 5,
                ),
            country: Yup.object()
                .shape({
                    label: Yup.string().required(),
                    value: Yup.string().required(),
                })
                .required(t('Please enter country'))
                .test(
                    'non-null-or-empty-string',
                    t('Please enter country'),
                    (value: { label: string; value: string }) => value.value !== '',
                ),
            companyName: Yup.string().when('customer', {
                is: (customer: string) => customer === 'companyCustomer',
                then: Yup.string().required(t('Please enter company name')),
                otherwise: Yup.string(),
            }),
            companyNumber: Yup.string().when('customer', {
                is: (customer: string) => customer === 'companyCustomer',
                then: Yup.string()
                    .required(t('Please enter identification number'))
                    .matches(/^[0-9]*$/, t('Please enter only numbers'))
                    .test(
                        'equals-8',
                        t('This value must be exactly 8 characters'),
                        (value) => value !== undefined && value.length === 8,
                    ),
                otherwise: Yup.string(),
            }),
            companyTaxNumber: Yup.string(),
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
    const isCompanyUser = formProviderMethods.formState.dirtyFields.isCompanyUser;
    const errors = formProviderMethods.formState.errors;

    const formMeta = {
        formName: 'customer-change-profile-form',
        messages: {
            error: t('Error occured while saving your profile'),
            success: t('Your profile has been changed successfully'),
        },
        fields: {
            isCompanyUser: {
                name: 'isCompanyUser' as const,
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
                errorMessage: isCompanyUser ? errors.companyName?.message : undefined,
            },
            companyNumber: {
                name: 'companyNumber' as const,
                label: t('Company number'),
                errorMessage: isCompanyUser ? errors.companyNumber?.message : undefined,
            },
            companyTaxNumber: {
                name: 'companyTaxNumber' as const,
                label: t('Tax number'),
                errorMessage: isCompanyUser ? errors.companyTaxNumber?.message : undefined,
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
    };

    return formMeta;
};

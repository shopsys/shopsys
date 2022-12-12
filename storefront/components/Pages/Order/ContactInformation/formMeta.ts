import { yupResolver } from '@hookform/resolvers/yup';
import { VALIDATION_CONSTANTS } from 'components/Forms/validationConstants';
import { useCurrentCart } from 'connectors/cart/Cart';
import { useShopsysForm } from 'hooks/forms/useShopsysForm';
import { useTypedTranslationFunction } from 'hooks/typescript/useTypedTranslationFunction';
import { useCurrentUserContactInformation } from 'hooks/user/useCurrentUserContactInformation';
import { useMemo } from 'react';
import { FieldError, UseFormReturn, useWatch } from 'react-hook-form';
import { CustomerTypeEnum } from 'types/customer';
import { ContactInformationFormType } from 'types/form';
import * as Yup from 'yup';

export const useContactInformationForm = (): [
    UseFormReturn<ContactInformationFormType>,
    ContactInformationFormType,
] => {
    const t = useTypedTranslationFunction();
    const contactInformationValues = useCurrentUserContactInformation();

    const resolver = yupResolver(
        Yup.object().shape({
            email: Yup.string()
                .required(t('Please enter email'))
                .email(t('This value is not a valid email'))
                .max(
                    VALIDATION_CONSTANTS.emailMaxLength,
                    t('email must be at most {{ max }} characters', { max: VALIDATION_CONSTANTS.emailMaxLength }),
                ),
            customer: Yup.string().oneOf(['commonCustomer', 'companyCustomer']),
            telephone: Yup.string()
                .required(t('Please enter phone number'))
                .matches(/^[0-9+]*$/, t('Please enter only numbers and the + character'))
                .test(
                    'more-than-8',
                    t('Telephone number cannot be shorter than {{ telephoneMinLength }} characters', {
                        telephoneMinLength: VALIDATION_CONSTANTS.telephoneMinLength,
                    }),
                    (value) => value !== undefined && value.length >= VALIDATION_CONSTANTS.telephoneMinLength,
                )
                .max(
                    VALIDATION_CONSTANTS.telephoneMaxLength,
                    t('telephone must be at most {{ max }} characters', {
                        max: VALIDATION_CONSTANTS.telephoneMaxLength,
                    }),
                ),
            firstName: Yup.string()
                .required(t('Please enter first name'))
                .max(
                    VALIDATION_CONSTANTS.firstNameMaxLength,
                    t('first name must be at most {{ max }} characters', {
                        max: VALIDATION_CONSTANTS.firstNameMaxLength,
                    }),
                ),
            lastName: Yup.string()
                .required(t('Please enter last name'))
                .max(
                    VALIDATION_CONSTANTS.lastNameMaxLength,
                    t('last name must be at most {{ max }} characters', {
                        max: VALIDATION_CONSTANTS.lastNameMaxLength,
                    }),
                ),
            street: Yup.string()
                .required(t('Please enter street'))
                .matches(/\D/, t('The street must contain a letter'))
                .matches(/\d/, t('The street must contain a number'))
                .max(
                    VALIDATION_CONSTANTS.streetMaxLength,
                    t('street must be at most {{ max }} characters', { max: VALIDATION_CONSTANTS.streetMaxLength }),
                ),
            city: Yup.string()
                .required(t('Please enter city'))
                .max(
                    VALIDATION_CONSTANTS.cityMaxLength,
                    t('city must be at most {{ max }} characters', { max: VALIDATION_CONSTANTS.streetMaxLength }),
                ),
            postcode: Yup.string()
                .required(t('Please enter zip code'))
                .test(
                    'less-than-or-equals-5',
                    t('Zip code cannot be longer than {{ postcodeLength }} characters', {
                        postcodeLength: VALIDATION_CONSTANTS.postcodeLength,
                    }),
                    (value) => value !== undefined && value.length <= VALIDATION_CONSTANTS.postcodeLength,
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
                then: Yup.string()
                    .required(t('Please enter company name'))
                    .max(
                        VALIDATION_CONSTANTS.companyNameMaxLength,
                        t('company name must be at most {{ max }} characters', {
                            max: VALIDATION_CONSTANTS.companyNameMaxLength,
                        }),
                    ),
                otherwise: Yup.string(),
            }),
            companyNumber: Yup.string().when('customer', {
                is: (customer: string) => customer === 'companyCustomer',
                then: Yup.string()
                    .required(t('Please enter identification number'))
                    .matches(/^[0-9]*$/, t('Please enter only numbers'))
                    .test(
                        'equals-8',
                        t('This value must be exactly {{ companyNumberLength }} characters', {
                            companyNumberLength: VALIDATION_CONSTANTS.companyNumberMaxLength,
                        }),
                        (value) => value !== undefined && value.length === VALIDATION_CONSTANTS.companyNumberMaxLength,
                    ),
                otherwise: Yup.string(),
            }),
            companyTaxNumber: Yup.string().max(
                        VALIDATION_CONSTANTS.companyTaxNumberMaxLength,
                        t('company tax number must be at most {{ max }} characters', {
                            max: VALIDATION_CONSTANTS.companyTaxNumberMaxLength,
                        }),
                    ),

            differentDeliveryAddress: Yup.boolean(),
            deliveryFirstName: Yup.string().when('differentDeliveryAddress', {
                is: true,
                then: Yup.string()
                    .required(t('Please enter first name of contact person'))
                    .max(
                        VALIDATION_CONSTANTS.firstNameMaxLength,
                        t('first name must be at most {{ max }} characters', {
                            max: VALIDATION_CONSTANTS.firstNameMaxLength,
                        }),
                    ),
                otherwise: Yup.string(),
            }),
            deliveryLastName: Yup.string().when('differentDeliveryAddress', {
                is: true,
                then: Yup.string()
                    .required(t('Please enter last name of contact person'))
                    .max(
                        VALIDATION_CONSTANTS.lastNameMaxLength,
                        t('last name must be at most {{ max }} characters', {
                            max: VALIDATION_CONSTANTS.lastNameMaxLength,
                        }),
                    ),
                otherwise: Yup.string(),
            }),
            deliveryCompanyName: Yup.string().max(
                VALIDATION_CONSTANTS.companyNameMaxLength,
                t('company name must be at most {{ max }} characters', {
                    max: VALIDATION_CONSTANTS.companyNameMaxLength,
                }),
            ),
            deliveryTelephone: Yup.string()
                .matches(/^[0-9+]*$/, t('Please enter only numbers and the + character'))
                .test(
                    'more-than-8-or-0',
                    t('Telephone number cannot be shorter than {{ telephoneMinLength }} characters', {
                        telephoneMinLength: VALIDATION_CONSTANTS.telephoneMinLength,
                    }),
                    (value) =>
                        (value !== undefined && value.length >= VALIDATION_CONSTANTS.telephoneMinLength) ||
                        (value !== undefined && value.length === 0),
                )
                .max(
                    VALIDATION_CONSTANTS.telephoneMaxLength,
                    t('telephone must be at most {{ max }} characters', {
                        max: VALIDATION_CONSTANTS.telephoneMaxLength,
                    }),
                ),
            deliveryStreet: Yup.string().when('differentDeliveryAddress', {
                is: true,
                then: Yup.string()
                    .required(t('Please enter street'))
                    .matches(/\D/, t('The street must contain a letter'))
                    .matches(/\d/, t('The street must contain a number'))
                    .max(
                        VALIDATION_CONSTANTS.streetMaxLength,
                        t('street must be at most {{ max }} characters', { max: VALIDATION_CONSTANTS.streetMaxLength }),
                    ),
                otherwise: Yup.string(),
            }),
            deliveryCity: Yup.string().when('differentDeliveryAddress', {
                is: true,
                then: Yup.string()
                    .required(t('Please enter city'))
                    .max(
                        VALIDATION_CONSTANTS.cityMaxLength,
                        t('city must be at most {{ max }} characters', { max: VALIDATION_CONSTANTS.cityMaxLength }),
                    ),
                otherwise: Yup.string(),
            }),
            deliveryPostcode: Yup.string().when('differentDeliveryAddress', {
                is: true,
                then: Yup.string()
                    .required(t('Please enter zip code'))
                    .test(
                        'less-than-or-equals-5',
                        t('Zip code cannot be longer than {{ postcodeLength }} characters', {
                            postcodeLength: VALIDATION_CONSTANTS.postcodeLength,
                        }),
                        (value) => value !== undefined && value.length <= VALIDATION_CONSTANTS.postcodeLength,
                    ),
                otherwise: Yup.string(),
            }),

            deliveryCountry: Yup.object().when('differentDeliveryAddress', {
                is: true,
                then: Yup.object()
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
            }),

            newsletterSubscription: Yup.boolean(),
        }),
    );
    const defaultValues = contactInformationValues;

    return [useShopsysForm(resolver, defaultValues), defaultValues];
};

type ContactInformationFormMetaType = {
    formName: string;
    messages: {
        error: string;
    };
    fields: {
        [key in keyof ContactInformationFormType]: {
            name: key;
            label: string;
            errorMessage: string | undefined;
        };
    };
};

export const useContactInformationFormMeta = (
    formProviderMethods: UseFormReturn<ContactInformationFormType>,
): ContactInformationFormMetaType => {
    const t = useTypedTranslationFunction();
    const { pickupPlace } = useCurrentCart();
    const isEmailValid = formProviderMethods.formState.errors.email === undefined;

    const differentDeliveryAddressFieldName = 'differentDeliveryAddress' as const;
    const customerFieldName = 'customer' as const;

    const [differentDeliveryAddressValue, customerValue] = useWatch({
        name: [differentDeliveryAddressFieldName, customerFieldName],
        control: formProviderMethods.control,
    });

    const errors = formProviderMethods.formState.errors;

    const formMeta = useMemo(
        () => ({
            formName: 'contact-information-form',
            messages: {
                error: t('Could not create order'),
            },
            fields: {
                email: {
                    name: 'email' as const,
                    label: t('Your email'),
                    errorMessage: errors.email?.message,
                },
                [customerFieldName]: {
                    name: customerFieldName,
                    label: t('You will shop with us as'),
                    errorMessage: isEmailValid ? errors.customer?.message : undefined,
                },
                telephone: {
                    name: 'telephone' as const,
                    label: t('Phone'),
                    errorMessage: isEmailValid ? errors.telephone?.message : undefined,
                },
                firstName: {
                    name: 'firstName' as const,
                    label: t('First name'),
                    errorMessage: isEmailValid ? errors.firstName?.message : undefined,
                },
                lastName: {
                    name: 'lastName' as const,
                    label: t('Last name'),
                    errorMessage: isEmailValid ? errors.lastName?.message : undefined,
                },
                companyName: {
                    name: 'companyName' as const,
                    label: t('Company name'),
                    errorMessage:
                        customerValue === CustomerTypeEnum.CompanyCustomer ? errors.companyName?.message : undefined,
                },
                companyNumber: {
                    name: 'companyNumber' as const,
                    label: t('Company number'),
                    errorMessage:
                        customerValue === CustomerTypeEnum.CompanyCustomer ? errors.companyNumber?.message : undefined,
                },
                companyTaxNumber: {
                    name: 'companyTaxNumber' as const,
                    label: t('Tax number'),
                    errorMessage:
                        customerValue === CustomerTypeEnum.CompanyCustomer
                            ? errors.companyTaxNumber?.message
                            : undefined,
                },
                street: {
                    name: 'street' as const,
                    label: t('Street and house no.'),
                    errorMessage: isEmailValid ? errors.street?.message : undefined,
                },
                city: {
                    name: 'city' as const,
                    label: t('City'),
                    errorMessage: isEmailValid ? errors.city?.message : undefined,
                },
                postcode: {
                    name: 'postcode' as const,
                    label: t('Postcode'),
                    errorMessage: isEmailValid ? errors.postcode?.message : undefined,
                },
                country: {
                    name: 'country' as const,
                    label: t('Country'),
                    errorMessage: isEmailValid ? (errors.country as FieldError | undefined)?.message : undefined,
                },
                [differentDeliveryAddressFieldName]: {
                    name: differentDeliveryAddressFieldName,
                    label: pickupPlace ? t('Enter the delivery information') : t('Enter the delivery address'),
                    errorMessage: isEmailValid ? errors.differentDeliveryAddress?.message : undefined,
                },
                deliveryFirstName: {
                    name: 'deliveryFirstName' as const,
                    label: t('First name'),
                    errorMessage: differentDeliveryAddressValue ? errors.deliveryFirstName?.message : undefined,
                },
                deliveryLastName: {
                    name: 'deliveryLastName' as const,
                    label: t('Last name'),
                    errorMessage: differentDeliveryAddressValue ? errors.deliveryLastName?.message : undefined,
                },
                deliveryCompanyName: {
                    name: 'deliveryCompanyName' as const,
                    label: t('Company'),
                    errorMessage: differentDeliveryAddressValue ? errors.deliveryCompanyName?.message : undefined,
                },
                deliveryTelephone: {
                    name: 'deliveryTelephone' as const,
                    label: t('Phone'),
                    errorMessage: differentDeliveryAddressValue ? errors.deliveryTelephone?.message : undefined,
                },
                deliveryStreet: {
                    name: 'deliveryStreet' as const,
                    label: t('Street and house number'),
                    errorMessage: differentDeliveryAddressValue ? errors.deliveryStreet?.message : undefined,
                },
                deliveryCity: {
                    name: 'deliveryCity' as const,
                    label: t('City'),
                    errorMessage: differentDeliveryAddressValue ? errors.deliveryCity?.message : undefined,
                },
                deliveryPostcode: {
                    name: 'deliveryPostcode' as const,
                    label: t('Postcode'),
                    errorMessage: differentDeliveryAddressValue ? errors.deliveryPostcode?.message : undefined,
                },
                deliveryCountry: {
                    name: 'deliveryCountry' as const,
                    label: t('Country'),
                    errorMessage: differentDeliveryAddressValue
                        ? (errors.deliveryCountry as FieldError | undefined)?.message
                        : undefined,
                },
                deliveryAddressUuid: {
                    name: 'deliveryAddressUuid' as const,
                    label: t('Delivery address'),
                    errorMessage: undefined,
                },
                newsletterSubscription: {
                    name: 'newsletterSubscription' as const,
                    label: t('I want to subscribe to the newsletter'),
                    errorMessage: isEmailValid ? errors.newsletterSubscription?.message : undefined,
                },
                note: {
                    name: 'note' as const,
                    label: t('Note'),
                    errorMessage: undefined,
                },
            },
        }),
        [
            errors.customer?.message,
            errors.telephone?.message,
            errors.firstName?.message,
            errors.lastName?.message,
            errors.companyName?.message,
            errors.street?.message,
            errors.city?.message,
            errors.postcode?.message,
            errors.differentDeliveryAddress?.message,
            errors.deliveryFirstName?.message,
            errors.deliveryLastName?.message,
            errors.deliveryCompanyName?.message,
            errors.deliveryTelephone?.message,
            errors.deliveryStreet?.message,
            errors.deliveryCity?.message,
            errors.companyNumber?.message,
            errors.companyTaxNumber?.message,
            errors.country,
            errors.deliveryCountry,
            errors.deliveryPostcode?.message,
            errors.email?.message,
            errors.newsletterSubscription?.message,
            pickupPlace,
            customerValue,
            differentDeliveryAddressValue,
            isEmailValid,
            t,
        ],
    );

    return formMeta;
};

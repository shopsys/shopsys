import { yupResolver } from '@hookform/resolvers/yup';
import {
    validateCity,
    validateCompanyNameRequired,
    validateCompanyNumber,
    validateCompanyTaxNumber,
    validateCountry,
    validateCustomer,
    validateEmail,
    validateFirstName,
    validateLastName,
    validatePostcode,
    validateStreet,
    validateTelephone,
    validateTelephoneRequired,
} from 'components/Forms/validationRules';
import { useCurrentCart } from 'hooks/cart/useCurrentCart';
import { useShopsysForm } from 'hooks/forms/useShopsysForm';
import { useCurrentUserContactInformation } from 'hooks/user/useCurrentUserContactInformation';
import useTranslation from 'next-translate/useTranslation';
import { useMemo } from 'react';
import { FieldError, UseFormReturn, useWatch } from 'react-hook-form';
import { ContactInformation } from 'store/slices/createContactInformationSlice';
import { CustomerTypeEnum } from 'types/customer';
import * as Yup from 'yup';

export const useContactInformationForm = (): [UseFormReturn<ContactInformation>, ContactInformation] => {
    const { t } = useTranslation();
    const contactInformationValues = useCurrentUserContactInformation();

    const resolver = yupResolver(
        Yup.object().shape<Record<keyof ContactInformation, any>>({
            email: validateEmail(t),
            customer: validateCustomer(),
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
            differentDeliveryAddress: Yup.boolean(),
            deliveryFirstName: Yup.string().when('differentDeliveryAddress', {
                is: true,
                then: validateFirstName(t),
                otherwise: Yup.string(),
            }),
            deliveryLastName: Yup.string().when('differentDeliveryAddress', {
                is: true,
                then: validateLastName(t),
                otherwise: Yup.string(),
            }),
            deliveryCompanyName: Yup.string(),
            deliveryTelephone: Yup.string().when('differentDeliveryAddress', {
                is: true,
                then: validateTelephone(t),
                otherwise: Yup.string(),
            }),
            deliveryStreet: Yup.string().when('differentDeliveryAddress', {
                is: true,
                then: validateStreet(t),
                otherwise: Yup.string(),
            }),
            deliveryCity: Yup.string().when('differentDeliveryAddress', {
                is: true,
                then: validateCity(t),
                otherwise: Yup.string(),
            }),
            deliveryPostcode: Yup.string().when('differentDeliveryAddress', {
                is: true,
                then: validatePostcode(t),
                otherwise: Yup.string(),
            }),

            deliveryCountry: Yup.object().when('differentDeliveryAddress', {
                is: true,
                then: validateCountry(t),
            }),
            newsletterSubscription: Yup.boolean(),
            deliveryAddressUuid: Yup.string().optional().nullable(),
            note: Yup.string().optional().nullable(),
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
        [key in keyof ContactInformation]: {
            name: key;
            label: string;
            errorMessage: string | undefined;
        };
    };
};

export const useContactInformationFormMeta = (
    formProviderMethods: UseFormReturn<ContactInformation>,
): ContactInformationFormMetaType => {
    const { t } = useTranslation();
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

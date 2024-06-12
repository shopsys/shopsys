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
import useTranslation from 'next-translate/useTranslation';
import { useMemo } from 'react';
import { FieldError, UseFormReturn, useWatch } from 'react-hook-form';
import { ContactInformation } from 'store/slices/createContactInformationSlice';
import { CustomerTypeEnum } from 'types/customer';
import { useIsUserLoggedIn } from 'utils/auth/useIsUserLoggedIn';
import { useCurrentCart } from 'utils/cart/useCurrentCart';
import { useShopsysForm } from 'utils/forms/useShopsysForm';
import { useCurrentUserContactInformation } from 'utils/user/useCurrentUserContactInformation';
import * as Yup from 'yup';

export const useContactInformationForm = (): [UseFormReturn<ContactInformation>, ContactInformation] => {
    const { t } = useTranslation();
    const isUserLoggedIn = useIsUserLoggedIn();
    const contactInformationValues = useCurrentUserContactInformation();
    const { pickupPlace } = useCurrentCart();

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
            isDeliveryAddressDifferentFromBilling: Yup.boolean(),
            deliveryFirstName: Yup.string().when(['isDeliveryAddressDifferentFromBilling', 'deliveryAddressUuid'], {
                is: (isDeliveryAddressDifferentFromBilling: boolean, deliveryAddressUuid: string) =>
                    shouldValidateDeliveryAddressField(
                        isUserLoggedIn,
                        isDeliveryAddressDifferentFromBilling,
                        deliveryAddressUuid,
                        !!pickupPlace,
                        true,
                    ),
                then: validateFirstName(t),
                otherwise: Yup.string(),
            }),
            deliveryLastName: Yup.string().when(['isDeliveryAddressDifferentFromBilling', 'deliveryAddressUuid'], {
                is: (isDeliveryAddressDifferentFromBilling: boolean, deliveryAddressUuid: string) =>
                    shouldValidateDeliveryAddressField(
                        isUserLoggedIn,
                        isDeliveryAddressDifferentFromBilling,
                        deliveryAddressUuid,
                        !!pickupPlace,
                        true,
                    ),
                then: validateLastName(t),
                otherwise: Yup.string(),
            }),
            deliveryCompanyName: Yup.string(),
            deliveryTelephone: Yup.string().when(['isDeliveryAddressDifferentFromBilling', 'deliveryAddressUuid'], {
                is: (isDeliveryAddressDifferentFromBilling: boolean, deliveryAddressUuid: string) =>
                    shouldValidateDeliveryAddressField(
                        isUserLoggedIn,
                        isDeliveryAddressDifferentFromBilling,
                        deliveryAddressUuid,
                        !!pickupPlace,
                        true,
                    ),
                then: validateTelephone(t),
                otherwise: Yup.string(),
            }),
            deliveryStreet: Yup.string().when(['isDeliveryAddressDifferentFromBilling', 'deliveryAddressUuid'], {
                is: (isDeliveryAddressDifferentFromBilling: boolean, deliveryAddressUuid: string) =>
                    shouldValidateDeliveryAddressField(
                        isUserLoggedIn,
                        isDeliveryAddressDifferentFromBilling,
                        deliveryAddressUuid,
                        !!pickupPlace,
                    ),
                then: validateStreet(t),
                otherwise: Yup.string(),
            }),
            deliveryCity: Yup.string().when(['isDeliveryAddressDifferentFromBilling', 'deliveryAddressUuid'], {
                is: (isDeliveryAddressDifferentFromBilling: boolean, deliveryAddressUuid: string) =>
                    shouldValidateDeliveryAddressField(
                        isUserLoggedIn,
                        isDeliveryAddressDifferentFromBilling,
                        deliveryAddressUuid,
                        !!pickupPlace,
                    ),
                then: validateCity(t),
                otherwise: Yup.string(),
            }),
            deliveryPostcode: Yup.string().when(['isDeliveryAddressDifferentFromBilling', 'deliveryAddressUuid'], {
                is: (isDeliveryAddressDifferentFromBilling: boolean, deliveryAddressUuid: string) =>
                    shouldValidateDeliveryAddressField(
                        isUserLoggedIn,
                        isDeliveryAddressDifferentFromBilling,
                        deliveryAddressUuid,
                        !!pickupPlace,
                    ),
                then: validatePostcode(t),
                otherwise: Yup.string(),
            }),
            deliveryCountry: Yup.object().when(['isDeliveryAddressDifferentFromBilling', 'deliveryAddressUuid'], {
                is: (isDeliveryAddressDifferentFromBilling: boolean, deliveryAddressUuid: string) =>
                    shouldValidateDeliveryAddressField(
                        isUserLoggedIn,
                        isDeliveryAddressDifferentFromBilling,
                        deliveryAddressUuid,
                        !!pickupPlace,
                    ),
                then: validateCountry(t),
            }),
            newsletterSubscription: Yup.boolean(),
            deliveryAddressUuid: Yup.string().optional(),
            note: Yup.string().optional().nullable(),
            isWithoutHeurekaAgreement: Yup.boolean(),
        }),
    );
    const defaultValues = {
        ...contactInformationValues,
        deliveryAddressUuid: pickupPlace ? '' : contactInformationValues.deliveryAddressUuid,
    };
    const formProviderMethods = useShopsysForm(resolver, defaultValues);

    return [formProviderMethods, defaultValues];
};

const shouldValidateDeliveryAddressField = (
    isUserLoggedIn: boolean,
    isDeliveryAddressDifferentFromBilling: boolean,
    deliveryAddressUuid: string,
    isPickupPlaceSelected?: boolean,
    isRelevantForPickupPlace?: boolean,
) => {
    if (!isDeliveryAddressDifferentFromBilling || !isRelevantForPickupPlace) {
        return false;
    }

    if (isPickupPlaceSelected) {
        return true;
    }

    if (isUserLoggedIn) {
        return deliveryAddressUuid === '';
    }

    return true;
};

export type ContactInformationFormMetaType = {
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

    const isDeliveryAddressDifferentFromBillingFieldName = 'isDeliveryAddressDifferentFromBilling' as const;
    const customerFieldName = 'customer' as const;

    const [isDeliveryAddressDifferentFromBillingValue, customerValue] = useWatch({
        name: [isDeliveryAddressDifferentFromBillingFieldName, customerFieldName],
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
                [isDeliveryAddressDifferentFromBillingFieldName]: {
                    name: isDeliveryAddressDifferentFromBillingFieldName,
                    label: pickupPlace ? t('Enter the delivery information') : t('Enter the delivery address'),
                    errorMessage: isEmailValid ? errors.isDeliveryAddressDifferentFromBilling?.message : undefined,
                },
                deliveryFirstName: {
                    name: 'deliveryFirstName' as const,
                    label: t('First name'),
                    errorMessage: isDeliveryAddressDifferentFromBillingValue
                        ? errors.deliveryFirstName?.message
                        : undefined,
                },
                deliveryLastName: {
                    name: 'deliveryLastName' as const,
                    label: t('Last name'),
                    errorMessage: isDeliveryAddressDifferentFromBillingValue
                        ? errors.deliveryLastName?.message
                        : undefined,
                },
                deliveryCompanyName: {
                    name: 'deliveryCompanyName' as const,
                    label: t('Company'),
                    errorMessage: isDeliveryAddressDifferentFromBillingValue
                        ? errors.deliveryCompanyName?.message
                        : undefined,
                },
                deliveryTelephone: {
                    name: 'deliveryTelephone' as const,
                    label: t('Phone'),
                    errorMessage: isDeliveryAddressDifferentFromBillingValue
                        ? errors.deliveryTelephone?.message
                        : undefined,
                },
                deliveryStreet: {
                    name: 'deliveryStreet' as const,
                    label: t('Street and house no.'),
                    errorMessage: isDeliveryAddressDifferentFromBillingValue
                        ? errors.deliveryStreet?.message
                        : undefined,
                },
                deliveryCity: {
                    name: 'deliveryCity' as const,
                    label: t('City'),
                    errorMessage: isDeliveryAddressDifferentFromBillingValue ? errors.deliveryCity?.message : undefined,
                },
                deliveryPostcode: {
                    name: 'deliveryPostcode' as const,
                    label: t('Postcode'),
                    errorMessage: isDeliveryAddressDifferentFromBillingValue
                        ? errors.deliveryPostcode?.message
                        : undefined,
                },
                deliveryCountry: {
                    name: 'deliveryCountry' as const,
                    label: t('Country'),
                    errorMessage: isDeliveryAddressDifferentFromBillingValue
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
                isWithoutHeurekaAgreement: {
                    name: 'isWithoutHeurekaAgreement' as const,
                    label: t(
                        'I do not agree to send satisfaction questionnaires within the Verified by Customers program',
                    ),
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
            errors.isDeliveryAddressDifferentFromBilling?.message,
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
            isDeliveryAddressDifferentFromBillingValue,
            isEmailValid,
            t,
        ],
    );

    return formMeta;
};

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
    validateTelephonePrefix,
    validateTelephoneRequired,
} from 'components/Forms/validationRules';
import { useAuthorization } from 'components/providers/AuthorizationProvider';
import { useEffect } from 'react';
import { UseFormReturn } from 'react-hook-form';
import { ContactInformation } from 'store/slices/createContactInformationSlice';
import { FormMeta } from 'types/formMeta';
import { useIsUserLoggedIn } from 'utils/auth/useIsUserLoggedIn';
import { useCurrentCart } from 'utils/cart/useCurrentCart';
import { createFields } from 'utils/forms/createFields';
import { useFormWrapper } from 'utils/forms/useFormWrapper';
import useTranslation from 'utils/i18n/useTranslationWrapper';
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
            telephonePrefix: validateTelephonePrefix(t),
            telephonePrefixCountryCode: Yup.string(),
            telephone: validateTelephoneRequired(t),
            firstName: validateFirstName(t),
            lastName: validateLastName(t),
            street: validateStreet(t),
            city: validateCity(t),
            postcode: validatePostcode(t),
            country: validateCountry(t),
            companyName: Yup.string().when('customer', {
                is: (customer: string) => customer === 'companyCustomer',
                then: () => validateCompanyNameRequired(t),
                otherwise: (schema) => schema,
            }),
            companyNumber: Yup.string().when('customer', {
                is: (customer: string) => customer === 'companyCustomer',
                then: () => validateCompanyNumber(t),
                otherwise: (schema) => schema,
            }),
            companyTaxNumber: Yup.string().when('customer', {
                is: (customer: string) => customer === 'companyCustomer',
                then: () => validateCompanyTaxNumber(t),
                otherwise: (schema) => schema,
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
                then: () => validateFirstName(t),
                otherwise: (schema) => schema,
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
                then: () => validateLastName(t),
                otherwise: (schema) => schema,
            }),
            deliveryCompanyName: Yup.string(),
            deliveryTelephonePrefix: Yup.string().when(
                ['isDeliveryAddressDifferentFromBilling', 'deliveryAddressUuid', 'deliveryTelephone'],
                {
                    is: (
                        isDeliveryAddressDifferentFromBilling: boolean,
                        deliveryAddressUuid: string,
                        deliveryTelephone: string,
                    ) =>
                        shouldValidateDeliveryAddressField(
                            isUserLoggedIn,
                            isDeliveryAddressDifferentFromBilling,
                            deliveryAddressUuid,
                            !!pickupPlace,
                            true,
                        ) && deliveryTelephone.length > 0,
                    then: () => validateTelephonePrefix(t),
                    otherwise: (schema) => schema,
                },
            ),
            deliveryTelephonePrefixCountryCode: Yup.string(),
            deliveryTelephone: Yup.string().when(['isDeliveryAddressDifferentFromBilling', 'deliveryAddressUuid'], {
                is: (isDeliveryAddressDifferentFromBilling: boolean, deliveryAddressUuid: string) =>
                    shouldValidateDeliveryAddressField(
                        isUserLoggedIn,
                        isDeliveryAddressDifferentFromBilling,
                        deliveryAddressUuid,
                        !!pickupPlace,
                        true,
                    ),
                then: () => validateTelephone(t),
                otherwise: (schema) => schema,
            }),
            deliveryStreet: Yup.string().when(['isDeliveryAddressDifferentFromBilling', 'deliveryAddressUuid'], {
                is: (isDeliveryAddressDifferentFromBilling: boolean, deliveryAddressUuid: string) =>
                    shouldValidateDeliveryAddressField(
                        isUserLoggedIn,
                        isDeliveryAddressDifferentFromBilling,
                        deliveryAddressUuid,
                        !!pickupPlace,
                        false,
                    ),
                then: () => validateStreet(t),
                otherwise: (schema) => schema,
            }),
            deliveryCity: Yup.string().when(['isDeliveryAddressDifferentFromBilling', 'deliveryAddressUuid'], {
                is: (isDeliveryAddressDifferentFromBilling: boolean, deliveryAddressUuid: string) =>
                    shouldValidateDeliveryAddressField(
                        isUserLoggedIn,
                        isDeliveryAddressDifferentFromBilling,
                        deliveryAddressUuid,
                        !!pickupPlace,
                        false,
                    ),
                then: () => validateCity(t),
                otherwise: (schema) => schema,
            }),
            deliveryPostcode: Yup.string().when(['isDeliveryAddressDifferentFromBilling', 'deliveryAddressUuid'], {
                is: (isDeliveryAddressDifferentFromBilling: boolean, deliveryAddressUuid: string) =>
                    shouldValidateDeliveryAddressField(
                        isUserLoggedIn,
                        isDeliveryAddressDifferentFromBilling,
                        deliveryAddressUuid,
                        !!pickupPlace,
                        false,
                    ),
                then: () => validatePostcode(t),
                otherwise: (schema) => schema,
            }),
            deliveryCountry: Yup.object().when(['isDeliveryAddressDifferentFromBilling', 'deliveryAddressUuid'], {
                is: (isDeliveryAddressDifferentFromBilling: boolean, deliveryAddressUuid: string) =>
                    shouldValidateDeliveryAddressField(
                        isUserLoggedIn,
                        isDeliveryAddressDifferentFromBilling,
                        deliveryAddressUuid,
                        !!pickupPlace,
                    ),
                then: () => validateCountry(t),
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
    const formProviderMethods = useFormWrapper(resolver, defaultValues);

    useEffect(() => {
        if (defaultValues.email && defaultValues.email.length > 0) {
            formProviderMethods.trigger('email', { shouldFocus: false });
        } else {
            formProviderMethods.clearErrors('email');
        }
    }, [defaultValues.email, formProviderMethods]);

    return [formProviderMethods, defaultValues];
};

const shouldValidateDeliveryAddressField = (
    isUserLoggedIn: boolean,
    isDeliveryAddressDifferentFromBilling: boolean,
    deliveryAddressUuid: string,
    isPickupPlaceSelected?: boolean,
    isRelevantForPickupPlace?: boolean,
) => {
    if (
        !isPickupPlaceSelected &&
        isDeliveryAddressDifferentFromBilling &&
        (deliveryAddressUuid === 'new-delivery-address' || deliveryAddressUuid === '')
    ) {
        return true;
    }

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

export type ContactInformationFormMetaType = FormMeta<ContactInformation, { error: string }, { disabled?: boolean }>;

export const useContactInformationFormMeta = (): ContactInformationFormMetaType => {
    const { t } = useTranslation();
    const { pickupPlace } = useCurrentCart();
    const isUserLoggedIn = useIsUserLoggedIn();
    const { isB2B, isCompanyUser } = useAuthorization();

    const isDeliveryAddressDifferentFromBillingLabel = pickupPlace
        ? t('Order will pick up someone else')
        : t('Send to another delivery address');

    return {
        formName: 'contact-information-form',
        messages: {
            error: t('Could not create order'),
        },
        fields: createFields<ContactInformation, { disabled?: boolean }>({
            email: { label: t('Your email'), disabled: isUserLoggedIn },
            customer: { label: t('I will shop as'), disabled: isB2B && isUserLoggedIn },
            telephonePrefix: t('Phone prefix'),
            telephonePrefixCountryCode: t('Country code'),
            telephone: t('Phone'),
            firstName: t('First name'),
            lastName: t('Last name'),
            companyName: { label: t('Company name'), disabled: isCompanyUser },
            companyNumber: { label: t('Company number'), disabled: isCompanyUser },
            companyTaxNumber: { label: t('Tax number'), disabled: isCompanyUser },
            street: t('Street and house no.'),
            city: t('City'),
            postcode: t('Postcode'),
            country: t('Country'),
            isDeliveryAddressDifferentFromBilling: { label: isDeliveryAddressDifferentFromBillingLabel },
            deliveryFirstName: t('First name'),
            deliveryLastName: t('Last name'),
            deliveryCompanyName: t('Company'),
            deliveryTelephonePrefix: t('Phone prefix'),
            deliveryTelephonePrefixCountryCode: t('Country code'),
            deliveryTelephone: t('Phone'),
            deliveryStreet: t('Street and house no.'),
            deliveryCity: t('City'),
            deliveryPostcode: t('Postcode'),
            deliveryCountry: t('Country'),
            deliveryAddressUuid: t('Delivery address'),
            newsletterSubscription: t('I want to subscribe to the newsletter'),
            note: t('Note'),
            isWithoutHeurekaAgreement: t(
                'I do not agree to send satisfaction questionnaires within the Verified by Customers program',
            ),
        }),
    };
};

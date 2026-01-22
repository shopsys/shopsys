import { yupResolver } from '@hookform/resolvers/yup';
import {
    validateCity,
    validateCountry,
    validateFirstName,
    validateLastName,
    validatePostcode,
    validateStreet,
    validateTelephoneRequired,
} from 'components/Forms/validationRules';
import { FieldError, UseFormReturn } from 'react-hook-form';
import { DeliveryAddressFormType } from 'types/form';
import { useFormWrapper } from 'utils/forms/useFormWrapper';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import * as Yup from 'yup';

export const useDeliveryAddressForm = (
    defaultValues: DeliveryAddressFormType,
): [UseFormReturn<DeliveryAddressFormType>, DeliveryAddressFormType | undefined] => {
    const { t } = useTranslation();

    const resolver = yupResolver(
        Yup.object().shape<Record<keyof DeliveryAddressFormType, any>>({
            firstName: validateFirstName(t),
            lastName: validateLastName(t),
            companyName: Yup.string(),
            telephone: validateTelephoneRequired(t),
            street: validateStreet(t),
            city: validateCity(t),
            postcode: validatePostcode(t),
            country: validateCountry(t),
        }),
    );

    return [useFormWrapper(resolver, defaultValues), defaultValues];
};

type DeliveryAddressFormMetaType = {
    formName: string;
    messages: {
        error: string;
        success: string;
    };
    fields: {
        [key in keyof DeliveryAddressFormType]: {
            name: key;
            label: string;
            errorMessage?: string;
        };
    };
};

export const useDeliveryAddressFormMeta = (
    formProviderMethods: UseFormReturn<DeliveryAddressFormType>,
): DeliveryAddressFormMetaType => {
    const { t } = useTranslation();
    const errors = formProviderMethods.formState.errors;

    return {
        formName: 'delivery-address-form',
        messages: {
            error: t('An error occurred while saving your profile'),
            success: t('Your delivery address has been changed successfully'),
        },
        fields: {
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
                label: t('Company'),
                errorMessage: errors.companyName?.message,
            },
            telephone: {
                name: 'telephone' as const,
                label: t('Phone'),
                errorMessage: errors.telephone?.message,
            },
            street: {
                name: 'street' as const,
                label: t('Street and house no.'),
                errorMessage: errors.street?.message,
            },
            city: {
                name: 'city' as const,
                label: t('City'),
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
        },
    };
};

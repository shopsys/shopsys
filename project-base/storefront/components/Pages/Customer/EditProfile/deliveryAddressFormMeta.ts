import { yupResolver } from '@hookform/resolvers/yup';
import {
    validateCity,
    validateCountry,
    validateFirstName,
    validateLastName,
    validatePostcode,
    validateStreet,
    validateTelephonePrefix,
    validateTelephoneRequired,
} from 'components/Forms/validationRules';
import { UseFormReturn } from 'react-hook-form';
import { DeliveryAddressFormType } from 'types/form';
import { FormMeta } from 'types/formMeta';
import { createFields } from 'utils/forms/createFields';
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
            telephonePrefix: validateTelephonePrefix(t),
            telephonePrefixCountryCode: Yup.string(),
            telephone: validateTelephoneRequired(t),
            street: validateStreet(t),
            city: validateCity(t),
            postcode: validatePostcode(t),
            country: validateCountry(t),
        }),
    );

    return [useFormWrapper(resolver, defaultValues), defaultValues];
};

export const useDeliveryAddressFormMeta = (): FormMeta<DeliveryAddressFormType, { error: string; success: string }> => {
    const { t } = useTranslation();
    return {
        formName: 'delivery-address-form',
        messages: {
            error: t('An error occurred while saving your profile'),
            success: t('Your delivery address has been changed successfully'),
        },
        fields: createFields<DeliveryAddressFormType>({
            firstName: t('First name'),
            lastName: t('Last name'),
            companyName: t('Company'),
            telephonePrefix: t('Phone prefix'),
            telephonePrefixCountryCode: t('Country code'),
            telephone: t('Phone'),
            street: t('Street and house no.'),
            city: t('City'),
            postcode: t('Postcode'),
            country: t('Country'),
        }),
    };
};

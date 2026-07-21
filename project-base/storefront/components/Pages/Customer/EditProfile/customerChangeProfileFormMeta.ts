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
    validateTelephonePrefix,
    validateTelephoneRequired,
} from 'components/Forms/validationRules';
import { UseFormReturn } from 'react-hook-form';
import { CustomerChangeProfileFormType } from 'types/form';
import { FormMeta } from 'types/formMeta';
import { createFields } from 'utils/forms/createFields';
import { useFormWrapper } from 'utils/forms/useFormWrapper';
import { yupResolver } from 'utils/forms/yupResolver';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import * as Yup from 'yup';

export const useCustomerChangeProfileForm = (
    defaultValues: CustomerChangeProfileFormType,
): [UseFormReturn<CustomerChangeProfileFormType>, CustomerChangeProfileFormType] => {
    const { t } = useTranslation();

    const resolver = yupResolver<CustomerChangeProfileFormType>(
        Yup.object().shape<Record<keyof CustomerChangeProfileFormType, any>>({
            email: validateEmail(t),
            telephonePrefix: validateTelephonePrefix(t),
            telephonePrefixCountryCode: Yup.string(),
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

export const useCustomerChangeProfileFormMeta = (): FormMeta<
    CustomerChangeProfileFormType,
    { error: string; success: string }
> => {
    const { t } = useTranslation();
    return {
        formName: 'customer-change-profile-form',
        messages: {
            error: t('An error occurred while saving your profile'),
            success: t('Your profile has been changed successfully'),
        },
        fields: createFields<CustomerChangeProfileFormType>({
            companyCustomer: '',
            email: t('Your email'),
            telephonePrefix: t('Phone prefix'),
            telephonePrefixCountryCode: '',
            telephone: t('Phone'),
            firstName: t('First name'),
            lastName: t('Last name'),
            companyName: t('Company name'),
            companyNumber: t('Company number'),
            companyTaxNumber: t('Tax number'),
            street: t('Street and house no.'),
            city: t('City'),
            postcode: t('Postcode'),
            country: t('Country'),
            newsletterSubscription: t('I agree to receive the newsletter'),
        }),
    };
};

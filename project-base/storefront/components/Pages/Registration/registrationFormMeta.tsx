import { yupResolver } from '@hookform/resolvers/yup';
import { Link, linkPlaceholderTwClass } from 'components/Basic/Link/Link';
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
    validatePassword,
    validatePasswordConfirm,
    validatePostcode,
    validatePrivacyPolicy,
    validateStreet,
    validateTelephonePrefix,
    validateTelephoneRequired,
} from 'components/Forms/validationRules';
import { useSettingsQuery } from 'graphql/requests/settings/queries/SettingsQuery.generated';
import Trans from 'next-translate/Trans';
import { UseFormReturn } from 'react-hook-form';
import { CustomerTypeEnum } from 'types/customer';
import { RegistrationFormType } from 'types/form';
import { FormMeta } from 'types/formMeta';
import { createFields } from 'utils/forms/createFields';
import { useFormWrapper } from 'utils/forms/useFormWrapper';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import * as Yup from 'yup';

export const useRegistrationForm = (): [UseFormReturn<RegistrationFormType>, RegistrationFormType] => {
    const { t } = useTranslation();
    const resolver = yupResolver(
        Yup.object().shape<Record<keyof RegistrationFormType, any>>({
            email: validateEmail(t),
            password: validatePassword(t),
            passwordConfirm: validatePasswordConfirm(t),
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
            gdprAgreement: validatePrivacyPolicy(t),
            newsletterSubscription: Yup.boolean(),
        }),
    );
    const defaultValues = {
        email: '',
        password: '',
        passwordConfirm: '',
        customer: CustomerTypeEnum.CommonCustomer,
        telephonePrefix: '',
        telephonePrefixCountryCode: '',
        telephone: '',
        firstName: '',
        lastName: '',
        street: '',
        city: '',
        postcode: '',
        country: { value: '', label: '' },
        companyName: '',
        companyNumber: '',
        companyTaxNumber: '',
        gdprAgreement: false,
        newsletterSubscription: false,
    };

    return [useFormWrapper(resolver, defaultValues), defaultValues];
};

export const useRegistrationFormMeta = (): FormMeta<RegistrationFormType, { error: string; success: string }> => {
    const { t } = useTranslation();
    const [{ data: settingsData }] = useSettingsQuery();
    const privacyPolicyArticleUrl = settingsData?.settings?.privacyPolicyArticleUrl;

    return {
        formName: 'registration-form',
        messages: {
            error: t('Could not create account'),
            success: t('Your account has been created and you are logged in now'),
        },
        fields: createFields<RegistrationFormType>({
            email: t('Your email'),
            password: t('Password'),
            passwordConfirm: t('Password again'),
            customer: t('I will shop as'),
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
            gdprAgreement: (
                <Trans
                    defaultTrans="I agree with <lnk1>processing of privacy policy</lnk1>."
                    i18nKey="GdprAgreementCheckbox"
                    components={{
                        lnk1: privacyPolicyArticleUrl ? (
                            <Link
                                aria-label={t('Go to privacy policy article', { ns: 'accessibility' })}
                                className="inline text-sm"
                                href={privacyPolicyArticleUrl}
                                target="_blank"
                            />
                        ) : (
                            <span className={linkPlaceholderTwClass} />
                        ),
                    }}
                />
            ),
            newsletterSubscription: t('I want to subscribe to the newsletter'),
        }),
    };
};

import { yupResolver } from '@hookform/resolvers/yup';
import { Link } from 'components/Basic/Link/Link';
import {
    validateCity,
    validateCompanyNameRequired,
    validateCompanyNumber,
    validateCompanyTaxNumber,
    validateCountry,
    validateCustomer,
    validateEmail,
    validateFirstName,
    validateFirstPassword,
    validateLastName,
    validatePostcode,
    validateSecondPassword,
    validateStreet,
    validateTelephoneRequired,
} from 'components/Forms/validationRules';
import { usePrivacyPolicyArticleUrlQueryApi } from 'graphql/generated';
import { useShopsysForm } from 'hooks/forms/useShopsysForm';
import Trans from 'next-translate/Trans';
import useTranslation from 'next-translate/useTranslation';
import { useMemo } from 'react';
import { FieldError, UseFormReturn, useWatch } from 'react-hook-form';
import { CustomerTypeEnum } from 'types/customer';
import { RegistrationFormType } from 'types/form';
import * as Yup from 'yup';

export const useRegistrationForm = (): [UseFormReturn<RegistrationFormType>, RegistrationFormType] => {
    const { t } = useTranslation();
    const resolver = yupResolver(
        Yup.object().shape({
            email: validateEmail(t),
            passwordFirst: validateFirstPassword(t),
            passwordSecond: validateSecondPassword(t),
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
            gdprAgreement: Yup.boolean().isTrue(t('You have to agree with our privacy policy')),
            newsletterSubscription: Yup.boolean(),
        }),
    );
    const defaultValues = {
        email: '',
        passwordFirst: '',
        passwordSecond: '',
        customer: CustomerTypeEnum.CommonCustomer,
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

    return [useShopsysForm(resolver, defaultValues), defaultValues];
};

type RegistrationFormMetaType = {
    formName: string;
    messages: {
        error: string;
        successAndLogged: string;
    };
    fields: {
        [key in keyof Omit<RegistrationFormType, 'passwordFirst' | 'passwordSecond'>]: {
            name: key;
            label: string | JSX.Element;
            errorMessage: string | undefined;
        };
    } & {
        passwordFirst: {
            name: 'passwordFirst';
            label: string;
            errorMessage: string | undefined;
        };
        passwordSecond: {
            name: 'passwordSecond';
            label: string;
            errorMessage: string | undefined;
        };
    };
};

export const useRegistrationFormMeta = (
    formProviderMethods: UseFormReturn<RegistrationFormType>,
): RegistrationFormMetaType => {
    const { t } = useTranslation();
    const isEmailValid = formProviderMethods.formState.errors.email === undefined;
    const [{ data: privacyPolicyArticleUrlData }] = usePrivacyPolicyArticleUrlQueryApi();
    const privacyPolicyArticleUrl = privacyPolicyArticleUrlData?.privacyPolicyArticle?.slug;

    const customerFieldName = 'customer' as const;

    const [customerValue] = useWatch({
        name: [customerFieldName],
        control: formProviderMethods.control,
    });

    const errors = formProviderMethods.formState.errors;

    const formMeta = useMemo(
        () => ({
            formName: 'registration-form',
            messages: {
                error: t('Could not create account'),
                successAndLogged: t('Your account has been created and you are logged in now'),
            },
            fields: {
                email: {
                    name: 'email' as const,
                    label: t('Your email'),
                    errorMessage: errors.email?.message,
                },
                passwordFirst: {
                    name: 'passwordFirst' as const,
                    label: t('Password'),
                    errorMessage: errors.passwordFirst?.message,
                },
                passwordSecond: {
                    name: 'passwordSecond' as const,
                    label: t('Password again'),
                    errorMessage: errors.passwordSecond?.message,
                },
                [customerFieldName]: {
                    name: customerFieldName,
                    label: t('You will shop with us as'),
                    errorMessage: errors.customer?.message,
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
                gdprAgreement: {
                    name: 'gdprAgreement' as const,
                    label: (
                        <Trans
                            defaultTrans="I agree with <lnk1>processing of privacy policy</lnk1>."
                            i18nKey="GdprAgreementCheckbox"
                            components={{
                                lnk1:
                                    privacyPolicyArticleUrl !== undefined ? (
                                        <Link isExternal href={privacyPolicyArticleUrl} target="_blank" />
                                    ) : (
                                        <span />
                                    ),
                            }}
                        />
                    ),
                    errorMessage: errors.gdprAgreement?.message,
                },
                newsletterSubscription: {
                    name: 'newsletterSubscription' as const,
                    label: t('I want to subscribe to the newsletter'),
                    errorMessage: isEmailValid ? errors.newsletterSubscription?.message : undefined,
                },
            },
        }),
        [
            t,
            errors.email?.message,
            errors.passwordFirst?.message,
            errors.passwordSecond?.message,
            errors.customer?.message,
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
            errors.gdprAgreement?.message,
            errors.newsletterSubscription?.message,
            customerValue,
            privacyPolicyArticleUrl,
            isEmailValid,
        ],
    );

    return formMeta;
};

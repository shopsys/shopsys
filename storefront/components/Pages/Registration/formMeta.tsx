import * as Yup from 'yup';
import { FieldError, UseFormReturn, useWatch } from 'react-hook-form';
import { CustomerTypeEnum } from 'types/customer';
import { getInternationalizedStaticUrls } from 'utils/getInternationalizedStaticUrls';
import Link from 'components/Basic/Link';
import { SelectOptionType } from 'types/selectOptions';
import Trans from 'next-translate/Trans';
import { useShopsysForm } from 'hooks/forms/UseShopsysForm';
import { useShopsysSelector } from 'redux/main';
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';
import { yupResolver } from '@hookform/resolvers/yup';

export type RegistrationFormType = {
    email: string;
    passwordFirst: string;
    passwordSecond: string;
    customer: CustomerTypeEnum;
    telephone: string;
    firstName: string;
    lastName: string;
    street: string;
    city: string;
    postcode: string;
    country: SelectOptionType;
    companyName: string;
    companyNumber: string;
    companyTaxNumber: string;
    gdprAgreement: boolean;
    newsletterSubscription: boolean;
};

export const useRegistrationForm = (): [UseFormReturn<RegistrationFormType>, RegistrationFormType] => {
    const t = useTypedTranslationFunction();
    const resolver = yupResolver(
        Yup.object().shape({
            email: Yup.string().required(t('Please enter email')).email(t('This value is not a valid email')).min(5),
            passwordFirst: Yup.string()
                .required(t('Please enter password'))
                .min(
                    6,
                    t('Password must be at least {{ count }} characters long', {
                        count: 6,
                    }),
                ),
            passwordSecond: Yup.string()
                .required(t('Please enter password'))
                .min(
                    6,
                    t('Password must be at least {{ count }} characters long', {
                        count: 6,
                    }),
                )
                .oneOf([Yup.ref('passwordFirst'), null], t('Passwords must match')),
            customer: Yup.string().oneOf(['commonCustomer', 'companyCustomer']),
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
        success: string;
        successAndLogged: string;
    };
    fields: {
        [key in keyof RegistrationFormType]: {
            name: key;
            label: string | JSX.Element;
            errorMessage: string | undefined;
        };
    };
};

export const useRegistrationFormMeta = (
    formProviderMethods: UseFormReturn<RegistrationFormType>,
): RegistrationFormMetaType => {
    const t = useTypedTranslationFunction();
    const isEmailValid = formProviderMethods.formState.errors.email === undefined;
    const { url } = useShopsysSelector((state) => state.domain);
    const [GdprUrl] = getInternationalizedStaticUrls(['/gdpr'], url);

    const customerFieldName = 'customer' as const;

    const [customerValue] = useWatch({
        name: [customerFieldName],
        control: formProviderMethods.control,
    });

    const errors = formProviderMethods.formState.errors;

    const formMeta = {
        formName: 'registration-form',
        messages: {
            error: t('Could not create account'),
            success: t('Your account has been created'),
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
                    customerValue === CustomerTypeEnum.CompanyCustomer ? errors.companyTaxNumber?.message : undefined,
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
                        i18nKey="GdprAgreementCheckbox"
                        defaultTrans="I agree with <lnk1>processing of privacy policy</lnk1>."
                        components={{
                            lnk1: <Link href={GdprUrl} linkType="external" target="_blank" />,
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
    };

    return formMeta;
};

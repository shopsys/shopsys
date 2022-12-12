import { yupResolver } from '@hookform/resolvers/yup';
import { Link } from 'components/Basic/Link/Link';
import { VALIDATION_CONSTANTS } from 'components/Forms/validationConstants';
import { useShopsysForm } from 'hooks/forms/useShopsysForm';
import { useGetPrivacyPolicyUrl } from 'hooks/routes/useGetPrivacyPolicyUrl';
import { useTypedTranslationFunction } from 'hooks/typescript/useTypedTranslationFunction';
import Trans from 'next-translate/Trans';
import { useMemo } from 'react';
import { FieldError, UseFormReturn, useWatch } from 'react-hook-form';
import { CustomerTypeEnum } from 'types/customer';
import { RegistrationFormType } from 'types/form';
import * as Yup from 'yup';

export const useRegistrationForm = (): [UseFormReturn<RegistrationFormType>, RegistrationFormType] => {
    const t = useTypedTranslationFunction();
    const resolver = yupResolver(
        Yup.object().shape({
            email: Yup.string().required(t('Please enter email')).email(t('This value is not a valid email')).min(5),
            passwordFirst: Yup.string()
                .required(t('Please enter password'))
                .min(
                    VALIDATION_CONSTANTS.passwordMinLength,
                    t('Password must be at least {{ count }} characters long', {
                        count: VALIDATION_CONSTANTS.passwordMinLength,
                    }),
                ),
            passwordSecond: Yup.string()
                .required(t('Please enter password'))
                .min(
                    VALIDATION_CONSTANTS.passwordMinLength,
                    t('Password must be at least {{ count }} characters long', {
                        count: VALIDATION_CONSTANTS.passwordMinLength,
                    }),
                )
                .oneOf([Yup.ref('passwordFirst'), null], t('Passwords must match')),
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
            city: Yup.string().required(t('Please enter city')).max(VALIDATION_CONSTANTS.cityMaxLength),
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
    const t = useTypedTranslationFunction();
    const isEmailValid = formProviderMethods.formState.errors.email === undefined;
    const gdprUrl = useGetPrivacyPolicyUrl();

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
                            i18nKey="GdprAgreementCheckbox"
                            defaultTrans="I agree with <lnk1>processing of privacy policy</lnk1>."
                            components={{
                                lnk1: <Link href={gdprUrl} linkType="external" target="_blank" />,
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
            errors.country,
            errors.gdprAgreement?.message,
            errors.newsletterSubscription?.message,
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
            isEmailValid,
            customerValue,
            gdprUrl,
            t,
        ],
    );

    return formMeta;
};

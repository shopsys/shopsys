import * as Yup from 'yup';
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';
import { yupResolver } from '@hookform/resolvers/yup';

export const getContactInformationFormResolver = () => {
    const t = useTypedTranslationFunction();

    return yupResolver(
        Yup.object().shape({
            email: Yup.string().required(t('Please enter email')).email(t('This value is not a valid email')),
            register: Yup.boolean(),
            passwordFirst: Yup.string().when('register', {
                is: true,
                then: Yup.string().required(t('Please enter password')),
                otherwise: Yup.string(),
            }),
            passwordSecond: Yup.string().when('register', {
                is: true,
                then: Yup.string().required(t('Please enter password')),
                otherwise: Yup.string(),
            }),
            customer: Yup.string().oneOf(['commonCustomer', 'companyCustomer']),
            telephone: Yup.string()
                .required(t('Please enter telephone number'))
                .matches(/^[0-9+]*$/, t('Please enter only numbers and the + character'))
                .test(
                    'more-than-8',
                    t('Telephone number cannot be shorter than 9 characters'),
                    (value) => value !== undefined && value.length >= 9,
                ),
            firstName: Yup.string().required(t('Please enter first name')),
            lastName: Yup.string().required(t('Please enter last name')),
            street: Yup.string().required(t('Please enter street')),
            city: Yup.string().required(t('Please enter city')),
            postcode: Yup.string().required(t('Please enter zip code')),
            country: Yup.string(),
            companyName: Yup.string().when('customer', {
                is: (customer: string) => customer === 'companyCustomer',
                then: Yup.string().required(t('Please enter company name')),
                otherwise: Yup.string(),
            }),
            companyNumber: Yup.string().when('customer', {
                is: (customer: string) => customer === 'companyCustomer',
                then: Yup.string().required(t('Please enter identification number')),
                otherwise: Yup.string(),
            }),
            companyTaxNumber: Yup.string(),
        }),
    );
};

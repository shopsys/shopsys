import * as Yup from 'yup';
import { Resolver } from 'react-hook-form';
import { TFunction } from 'next-i18next';
import { yupResolver } from '@hookform/resolvers/yup';

export const getContactInformationFormResolver = <T>(t: TFunction): Resolver<T> => {
    return yupResolver(
        Yup.object().shape({
            email: Yup.string().required(t('Please enter email')).email(t('This value is not a valid email')).min(5),
            register: Yup.boolean(),
            passwordFirst: Yup.string().when('register', {
                is: true,
                then: Yup.string()
                    .required(t('Please enter password'))
                    .min(
                        6,
                        t('Password must be at least {{ count }} characters long', {
                            postProcess: 'interval',
                            count: 6,
                        }),
                    ),
                otherwise: Yup.string(),
            }),
            passwordSecond: Yup.string().when('register', {
                is: true,
                then: Yup.string()
                    .required(t('Please enter password'))
                    .min(
                        6,
                        t('Password must be at least {{ count }} characters long', {
                            postProcess: 'interval',
                            count: 6,
                        }),
                    ),
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
            street: Yup.string()
                .required(t('Please enter street'))
                .matches(/\D/, t('The street must contain a letter'))
                .matches(/\d/, t('The street must contain a number')),
            city: Yup.string().required(t('Please enter city')),
            postcode: Yup.string()
                .required(t('Please enter zip code'))
                .test(
                    'less-or-equals-than-5',
                    t('Zip code cannot be longer than 5 characters'),
                    (value) => value !== undefined && value.length <= 5,
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
            differentDeliveryAddress: Yup.boolean(),
            deliveryFirstName: Yup.string().when('differentDeliveryAddress', {
                is: true,
                then: Yup.string().required(t('Please enter first name of contact person')),
                otherwise: Yup.string(),
            }),
            deliveryLastName: Yup.string().when('differentDeliveryAddress', {
                is: true,
                then: Yup.string().required(t('Please enter last name of contact person')),
                otherwise: Yup.string(),
            }),
            deliveryCompanyName: Yup.string(),
            deliveryTelephone: Yup.string()
                .matches(/^[0-9+]*$/, t('Please enter only numbers and the + character'))
                .test(
                    'more-than-8-or-0',
                    t('Telephone number cannot be shorter than 9 characters'),
                    (value) =>
                        (value !== undefined && value.length >= 9) || (value !== undefined && value.length === 0),
                ),
            deliveryStreet: Yup.string().when('differentDeliveryAddress', {
                is: true,
                then: Yup.string()
                    .required(t('Please enter street'))
                    .matches(/\D/, t('The street must contain a letter'))
                    .matches(/\d/, t('The street must contain a number')),
                otherwise: Yup.string(),
            }),
            deliveryCity: Yup.string().when('differentDeliveryAddress', {
                is: true,
                then: Yup.string().required(t('Please enter city')),
                otherwise: Yup.string(),
            }),
            deliveryPostcode: Yup.string().when('differentDeliveryAddress', {
                is: true,
                then: Yup.string()
                    .required(t('Please enter zip code'))
                    .test(
                        'less-or-equals-than-5',
                        t('Zip code cannot be longer than 5 characters'),
                        (value) => value !== undefined && value.length <= 5,
                    ),
                otherwise: Yup.string(),
            }),
            newsletterSubscription: Yup.boolean(),
        }),
    );
};

import { yupResolver } from '@hookform/resolvers/yup';
import {
    validateCity,
    validateCountry,
    validateFirstName,
    validateImageFile,
    validateLastName,
    validatePostcode,
    validateStreet,
    validateTelephoneRequired,
} from 'components/Forms/validationRules';
import useTranslation from 'next-translate/useTranslation';
import { useMemo } from 'react';
import { FieldError, UseFormReturn } from 'react-hook-form';
import { ComplaintFormType } from 'types/form';
import { useShopsysForm } from 'utils/forms/useShopsysForm';
import * as Yup from 'yup';

export const useComplaintForm = (
    defaultDeliveryAddressChecked: string,
): [UseFormReturn<ComplaintFormType>, ComplaintFormType | undefined] => {
    const { t } = useTranslation();

    const resolver = yupResolver(
        Yup.object().shape<Record<keyof ComplaintFormType, any>>({
            quantity: Yup.string()
                .matches(/^[0-9]*$/, t('Please enter quantity'))
                .required(t('Please enter quantity')),
            description: Yup.string().required(t('Please enter description')),
            files: validateImageFile(t),
            deliveryAddressUuid: Yup.string().nullable(),
            firstName: Yup.string().when('deliveryAddressUuid', {
                is: (deliveryAddressUuid: string) => deliveryAddressUuid === '',
                then: validateFirstName(t),
                otherwise: Yup.string(),
            }),
            lastName: Yup.string().when('deliveryAddressUuid', {
                is: (deliveryAddressUuid: string) => deliveryAddressUuid === '',
                then: validateLastName(t),
                otherwise: Yup.string(),
            }),
            companyName: Yup.string().optional(),
            telephone: Yup.string().when('deliveryAddressUuid', {
                is: (deliveryAddressUuid: string) => deliveryAddressUuid === '',
                then: validateTelephoneRequired(t),
                otherwise: Yup.string(),
            }),
            street: Yup.string().when('deliveryAddressUuid', {
                is: (deliveryAddressUuid: string) => deliveryAddressUuid === '',
                then: validateStreet(t),
                otherwise: Yup.string(),
            }),
            city: Yup.string().when('deliveryAddressUuid', {
                is: (deliveryAddressUuid: string) => deliveryAddressUuid === '',
                then: validateCity(t),
                otherwise: Yup.string(),
            }),
            postcode: Yup.string().when('deliveryAddressUuid', {
                is: (deliveryAddressUuid: string) => deliveryAddressUuid === '',
                then: validatePostcode(t),
                otherwise: Yup.string(),
            }),
            country: Yup.object().when('deliveryAddressUuid', {
                is: (deliveryAddressUuid: string) => deliveryAddressUuid === '',
                then: validateCountry(t),
            }),
        }),
    );

    const defaultValues = {
        quantity: '1',
        description: '',
        files: [],
        deliveryAddressUuid: defaultDeliveryAddressChecked,
        firstName: '',
        lastName: '',
        companyName: '',
        telephone: '',
        street: '',
        city: '',
        postcode: '',
        country: {
            label: '',
            value: '',
        },
    };

    return [useShopsysForm<ComplaintFormType>(resolver, defaultValues), defaultValues];
};

export type ComplaintFormMetaType = {
    formName: string;
    messages: {
        error: string;
    };
    fields: {
        [key in keyof ComplaintFormType]: {
            name: key;
            label: string;
            errorMessage: string | undefined;
        };
    };
};

export const useComplaintFormMeta = (formProviderMethods: UseFormReturn<ComplaintFormType>): ComplaintFormMetaType => {
    const { t } = useTranslation();

    const errors = formProviderMethods.formState.errors;

    const formMeta = useMemo(
        () => ({
            formName: 'complaint-form',
            messages: {
                error: t('Could not create complaint'),
            },
            fields: {
                quantity: {
                    name: 'quantity' as const,
                    label: t('Quantity'),
                    errorMessage: errors.quantity?.message,
                },
                description: {
                    name: 'description' as const,
                    label: t('Description'),
                    errorMessage: errors.description?.message,
                },
                files: {
                    name: 'files' as const,
                    label: t('Files'),
                    errorMessage: errors.files?.message,
                },
                deliveryAddressUuid: {
                    name: 'deliveryAddressUuid' as const,
                    label: t('Delivery address'),
                    errorMessage: undefined,
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
            },
        }),
        [
            errors.quantity?.message,
            errors.description?.message,
            errors.files?.message,
            errors.firstName?.message,
            errors.lastName?.message,
            errors.companyName?.message,
            errors.telephone?.message,
            errors.street?.message,
            errors.city?.message,
            errors.postcode?.message,
            errors.country,
            t,
        ],
    );

    return formMeta;
};

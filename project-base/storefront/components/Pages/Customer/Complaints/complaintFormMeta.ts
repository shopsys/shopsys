import { yupResolver } from '@hookform/resolvers/yup';
import {
    validateBankAccountNumber,
    validateCity,
    validateCompanyName,
    validateComplaintManualDocumentNumber,
    validateCountry,
    validateEmail,
    validateFirstName,
    validateImageFile,
    validateLastName,
    validateManualComplaintItemCatnum,
    validateManualComplaintItemName,
    validatePostcode,
    validateResolution,
    validateStreet,
    validateTelephoneRequired,
} from 'components/Forms/validationRules';
import useTranslation from 'next-translate/useTranslation';
import { useMemo } from 'react';
import { FieldError, UseFormReturn } from 'react-hook-form';
import { ComplaintFormType } from 'types/form';
import { SelectOptionType } from 'types/selectOptions';
import { isResolutionMoneyReturn } from 'utils/complaints/isResolutionMoneyReturn';
import { useShopsysForm } from 'utils/forms/useShopsysForm';
import * as Yup from 'yup';

export const useComplaintForm = (
    defaultDeliveryAddressChecked: string,
    defaultEmail: string,
    isCreationWithoutOrder: boolean,
): [UseFormReturn<ComplaintFormType>, ComplaintFormType | undefined] => {
    const { t } = useTranslation();

    const resolver = yupResolver(
        Yup.object().shape<Record<keyof ComplaintFormType, any>>({
            quantity: Yup.string()
                .matches(/^[1-9][0-9]*$/, t('Please enter quantity'))
                .required(t('Please enter quantity')),
            description: Yup.string().required(t('Please enter description')),
            files: validateImageFile(t),
            deliveryAddressUuid: Yup.string().nullable(),
            email: validateEmail(t),
            resolution: validateResolution(t),
            bankAccountNumber: Yup.string().when('resolution', {
                is: (resolution: SelectOptionType) => isResolutionMoneyReturn(resolution),
                then: () => validateBankAccountNumber(t),
                otherwise: (schema) => schema,
            }),
            firstName: Yup.string().when('deliveryAddressUuid', {
                is: (deliveryAddressUuid: string) => deliveryAddressUuid === '',
                then: () => validateFirstName(t),
                otherwise: (schema) => schema,
            }),
            lastName: Yup.string().when('deliveryAddressUuid', {
                is: (deliveryAddressUuid: string) => deliveryAddressUuid === '',
                then: () => validateLastName(t),
                otherwise: (schema) => schema,
            }),
            companyName: validateCompanyName(t).optional(),
            telephone: Yup.string().when('deliveryAddressUuid', {
                is: (deliveryAddressUuid: string) => deliveryAddressUuid === '',
                then: () => validateTelephoneRequired(t),
                otherwise: (schema) => schema,
            }),
            street: Yup.string().when('deliveryAddressUuid', {
                is: (deliveryAddressUuid: string) => deliveryAddressUuid === '',
                then: () => validateStreet(t),
                otherwise: (schema) => schema,
            }),
            city: Yup.string().when('deliveryAddressUuid', {
                is: (deliveryAddressUuid: string) => deliveryAddressUuid === '',
                then: () => validateCity(t),
                otherwise: (schema) => schema,
            }),
            postcode: Yup.string().when('deliveryAddressUuid', {
                is: (deliveryAddressUuid: string) => deliveryAddressUuid === '',
                then: () => validatePostcode(t),
                otherwise: (schema) => schema,
            }),
            country: Yup.object().when('deliveryAddressUuid', {
                is: (deliveryAddressUuid: string) => deliveryAddressUuid === '',
                then: () => validateCountry(t),
            }),
            manualDocumentNumber: isCreationWithoutOrder
                ? validateComplaintManualDocumentNumber(t)
                : Yup.string().optional(),
            manualComplaintItemName: isCreationWithoutOrder
                ? validateManualComplaintItemName(t)
                : Yup.string().optional(),
            manualComplaintItemCatnum: isCreationWithoutOrder
                ? validateManualComplaintItemCatnum(t)
                : Yup.string().optional(),
        }),
    );

    const defaultValues = {
        quantity: '1',
        description: '',
        files: [],
        email: defaultEmail,
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
        manualDocumentNumber: '',
        manualComplaintItemName: '',
        manualComplaintItemCatnum: '',
        resolution: {
            label: '',
            value: '',
        },
        bankAccountNumber: '',
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
                email: {
                    name: 'email' as const,
                    label: t('Email'),
                    errorMessage: errors.email?.message,
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
                manualDocumentNumber: {
                    name: 'manualDocumentNumber' as const,
                    label: t('Order or document number'),
                    errorMessage: errors.manualDocumentNumber?.message,
                },
                manualComplaintItemName: {
                    name: 'manualComplaintItemName' as const,
                    label: t('Item name'),
                    errorMessage: errors.manualComplaintItemName?.message,
                },
                manualComplaintItemCatnum: {
                    name: 'manualComplaintItemCatnum' as const,
                    label: t('Catalog number'),
                    errorMessage: errors.manualComplaintItemCatnum?.message,
                },
                resolution: {
                    name: 'resolution' as const,
                    label: t('Resolution'),
                    errorMessage: errors.resolution?.message,
                },
                bankAccountNumber: {
                    name: 'bankAccountNumber' as const,
                    label: t('Bank account number'),
                    errorMessage: errors.bankAccountNumber?.message,
                },
            },
        }),
        [
            errors.quantity?.message,
            errors.description?.message,
            errors.bankAccountNumber?.message,
            errors.files?.message,
            errors.email?.message,
            errors.firstName?.message,
            errors.lastName?.message,
            errors.companyName?.message,
            errors.telephone?.message,
            errors.street?.message,
            errors.city?.message,
            errors.postcode?.message,
            errors.country,
            errors.manualDocumentNumber?.message,
            errors.manualComplaintItemName?.message,
            errors.manualComplaintItemCatnum?.message,
            errors.resolution,
            t,
        ],
    );

    return formMeta;
};

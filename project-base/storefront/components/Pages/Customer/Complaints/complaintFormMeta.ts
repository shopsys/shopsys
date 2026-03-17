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
import { UseFormReturn } from 'react-hook-form';
import { ComplaintFormType } from 'types/form';
import { FormMeta } from 'types/formMeta';
import { SelectOptionType } from 'types/selectOptions';
import { isResolutionMoneyReturn } from 'utils/complaints/isResolutionMoneyReturn';
import { createFields } from 'utils/forms/createFields';
import { useFormWrapper } from 'utils/forms/useFormWrapper';
import useTranslation from 'utils/i18n/useTranslationWrapper';
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

    return [useFormWrapper<ComplaintFormType>(resolver, defaultValues), defaultValues];
};

export const useComplaintFormMeta = (): FormMeta<ComplaintFormType, { error: string }> => {
    const { t } = useTranslation();

    return {
        formName: 'complaint-form',
        messages: {
            error: t('Could not create complaint'),
        },
        fields: createFields<ComplaintFormType>({
            quantity: t('Quantity', { ns: 'accessibility' }),
            description: t('Description'),
            files: t('Files'),
            email: t('Email'),
            deliveryAddressUuid: t('Delivery address'),
            firstName: t('First name'),
            lastName: t('Last name'),
            companyName: t('Company'),
            telephone: t('Phone'),
            street: t('Street and house no.'),
            city: t('City'),
            postcode: t('Postcode'),
            country: t('Country'),
            manualDocumentNumber: t('Order or document number'),
            manualComplaintItemName: t('Item name'),
            manualComplaintItemCatnum: t('Catalog number'),
            resolution: t('Resolution'),
            bankAccountNumber: t('Bank account number'),
        }),
    };
};

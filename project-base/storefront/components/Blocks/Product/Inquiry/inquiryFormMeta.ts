import { yupResolver } from '@hookform/resolvers/yup';
import {
    validateCompanyNumber,
    validateCompanyTaxNumber,
    validateEmail,
    validateFirstName,
    validateLastName,
    validateTelephoneRequired,
} from 'components/Forms/validationRules';
import useTranslation from 'next-translate/useTranslation';
import { useMemo } from 'react';
import { UseFormReturn } from 'react-hook-form';
import { InquiryFormType } from 'types/form';
import { useShopsysForm } from 'utils/forms/useShopsysForm';
import * as Yup from 'yup';

export const useInquiryForm = (
    defaultValues: InquiryFormType,
): [UseFormReturn<InquiryFormType>, InquiryFormType | undefined] => {
    const { t } = useTranslation();

    const resolver = yupResolver(
        Yup.object().shape<Record<keyof InquiryFormType, any>>({
            email: validateEmail(t),
            firstName: validateFirstName(t),
            lastName: validateLastName(t),
            telephone: validateTelephoneRequired(t),
            companyName: Yup.string().nullable(),
            companyNumber: Yup.string().when('companyName', {
                is: (companyName: string) => companyName.length > 0,
                then: () => validateCompanyNumber(t),
                otherwise: (schema) => schema,
            }),
            companyTaxNumber: validateCompanyTaxNumber(t),
            note: Yup.string().optional().nullable(),
            productUuid: Yup.string().required(),
        }),
    );

    return [useShopsysForm(resolver, defaultValues), defaultValues];
};

type InquiryFormMetaType = {
    formName: string;
    messages: {
        error: string;
    };
    fields: {
        [key in keyof InquiryFormType]: {
            name: key;
            label: string;
            errorMessage?: string;
        };
    };
};

export const useInquiryFormMeta = (formProviderMethods: UseFormReturn<InquiryFormType>): InquiryFormMetaType => {
    const { t } = useTranslation();
    const errors = formProviderMethods.formState.errors;

    const formMeta = useMemo(
        () => ({
            formName: 'inquiry-form',
            messages: {
                error: t('An error occurred while creating your inquiry'),
            },
            fields: {
                email: {
                    name: 'email' as const,
                    label: t('Your email'),
                    errorMessage: errors.email?.message,
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
                telephone: {
                    name: 'telephone' as const,
                    label: t('Phone'),
                    errorMessage: errors.telephone?.message,
                },
                companyName: {
                    name: 'companyName' as const,
                    label: t('Company'),
                    errorMessage: errors.companyName?.message,
                },
                companyNumber: {
                    name: 'companyNumber' as const,
                    label: t('Company number'),
                    errorMessage: errors.companyNumber?.message,
                },
                companyTaxNumber: {
                    name: 'companyTaxNumber' as const,
                    label: t('Tax number'),
                    errorMessage: errors.companyTaxNumber?.message,
                },
                note: {
                    name: 'note' as const,
                    label: t('Question'),
                    errorMessage: errors.note?.message,
                },
                productUuid: {
                    name: 'productUuid' as const,
                    label: t('Product'),
                    errorMessage: errors.productUuid?.message,
                },
            },
        }),
        [
            errors.firstName?.message,
            errors.lastName?.message,
            errors.companyName?.message,
            errors.telephone?.message,
            errors.companyNumber?.message,
            errors.companyTaxNumber?.message,
            errors.note?.message,
            errors.productUuid?.message,
            t,
        ],
    );
    return formMeta;
};

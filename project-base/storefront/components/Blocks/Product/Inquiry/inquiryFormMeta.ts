import { yupResolver } from '@hookform/resolvers/yup';
import {
    validateCompanyNumber,
    validateCompanyTaxNumber,
    validateEmail,
    validateFirstName,
    validateLastName,
    validateTelephoneRequired,
} from 'components/Forms/validationRules';
import { UseFormReturn } from 'react-hook-form';
import { InquiryFormType } from 'types/form';
import { FormMeta } from 'types/formMeta';
import { createFields } from 'utils/forms/createFields';
import { useFormWrapper } from 'utils/forms/useFormWrapper';
import useTranslation from 'utils/i18n/useTranslationWrapper';
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

    return [useFormWrapper(resolver, defaultValues), defaultValues];
};

export const useInquiryFormMeta = (): FormMeta<InquiryFormType, { error: string }> => {
    const { t } = useTranslation();
    return {
        formName: 'inquiry-form',
        messages: {
            error: t('An error occurred while creating your inquiry'),
        },
        fields: createFields<InquiryFormType>({
            email: t('Your email'),
            firstName: t('First name'),
            lastName: t('Last name'),
            telephone: t('Phone'),
            companyName: t('Company'),
            companyNumber: t('Company number'),
            companyTaxNumber: t('Tax number'),
            note: t('Question'),
            productUuid: t('Product'),
        }),
    };
};

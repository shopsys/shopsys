import { yupResolver } from '@hookform/resolvers/yup';
import { useShopsysForm } from 'hooks/forms/useShopsysForm';
import useTranslation from 'next-translate/useTranslation';
import { useMemo } from 'react';
import { UseFormReturn } from 'react-hook-form';
import { PersonalDataExportFormType } from 'types/form';
import * as Yup from 'yup';

export const usePersonalDataExportForm = (): [
    UseFormReturn<PersonalDataExportFormType>,
    PersonalDataExportFormType,
] => {
    const { t } = useTranslation();
    const resolver = yupResolver(
        Yup.object().shape<Record<keyof PersonalDataExportFormType, any>>({
            email: Yup.string().required(t('This field is required')).email(t('This value is not a valid email')),
        }),
    );
    const defaultValues = { email: '' };

    return [useShopsysForm(resolver, defaultValues), defaultValues];
};

type PersonalDataExportFormMetaType = {
    formName: string;
    messages: {
        error: string;
        success: string;
    };
    fields: {
        [key in keyof PersonalDataExportFormType]: {
            name: key;
            label: string;
            errorMessage: string | undefined;
        };
    };
};

export const usePersonalDataExportFormMeta = (
    formProviderMethods: UseFormReturn<PersonalDataExportFormType>,
): PersonalDataExportFormMetaType => {
    const { t } = useTranslation();

    const formMeta = useMemo(
        () => ({
            formName: 'personal-data-export-form',
            messages: {
                error: t('Could not sent personal data export request'),
                success: t('We sent an email with link to export your personal data'),
            },
            fields: {
                email: {
                    name: 'email' as const,
                    label: t('Your email'),
                    errorMessage: formProviderMethods.formState.errors.email?.message,
                },
            },
        }),
        [formProviderMethods.formState.errors.email?.message, t],
    );

    return formMeta;
};

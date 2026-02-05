import { yupResolver } from '@hookform/resolvers/yup';
import { validateEmail } from 'components/Forms/validationRules';
import { UseFormReturn } from 'react-hook-form';
import { PersonalDataExportFormType } from 'types/form';
import { useFormWrapper } from 'utils/forms/useFormWrapper';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import * as Yup from 'yup';

export const usePersonalDataExportForm = (): [
    UseFormReturn<PersonalDataExportFormType>,
    PersonalDataExportFormType,
] => {
    const { t } = useTranslation();
    const resolver = yupResolver(
        Yup.object().shape<Record<keyof PersonalDataExportFormType, any>>({
            email: validateEmail(t),
        }),
    );
    const defaultValues = { email: '' };

    return [useFormWrapper(resolver, defaultValues), defaultValues];
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
    const errors = formProviderMethods.formState.errors;

    return {
        formName: 'personal-data-export-form',
        messages: {
            error: t('Could not sent personal data export request'),
            success: t('We sent an email with link to export your personal data'),
        },
        fields: {
            email: {
                name: 'email' as const,
                label: t('Your email'),
                errorMessage: errors.email?.message,
            },
        },
    };
};

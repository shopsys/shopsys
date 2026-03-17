import { yupResolver } from '@hookform/resolvers/yup';
import { validateEmail } from 'components/Forms/validationRules';
import { UseFormReturn } from 'react-hook-form';
import { PersonalDataExportFormType } from 'types/form';
import { FormMeta } from 'types/formMeta';
import { createFields } from 'utils/forms/createFields';
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

export const usePersonalDataExportFormMeta = (): FormMeta<
    PersonalDataExportFormType,
    { error: string; success: string }
> => {
    const { t } = useTranslation();
    return {
        formName: 'personal-data-export-form',
        messages: {
            error: t('Could not sent personal data export request'),
            success: t('We sent an email with link to export your personal data'),
        },
        fields: createFields<PersonalDataExportFormType>({
            email: t('Your email'),
        }),
    };
};

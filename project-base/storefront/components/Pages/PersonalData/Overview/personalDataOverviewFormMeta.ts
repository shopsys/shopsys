import { yupResolver } from '@hookform/resolvers/yup';
import { validateEmail } from 'components/Forms/validationRules';
import { UseFormReturn } from 'react-hook-form';
import { PersonalDataOverviewFormType } from 'types/form';
import { FormMeta } from 'types/formMeta';
import { createFields } from 'utils/forms/createFields';
import { useFormWrapper } from 'utils/forms/useFormWrapper';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import * as Yup from 'yup';

export const usePersonalDataOverviewForm = (): [
    UseFormReturn<PersonalDataOverviewFormType>,
    PersonalDataOverviewFormType,
] => {
    const { t } = useTranslation();
    const resolver = yupResolver(
        Yup.object().shape<Record<keyof PersonalDataOverviewFormType, any>>({
            email: validateEmail(t),
        }),
    );
    const defaultValues = { email: '' };

    return [useFormWrapper(resolver, defaultValues), defaultValues];
};

export const usePersonalDataOverviewFormMeta = (): FormMeta<
    PersonalDataOverviewFormType,
    { error: string; success: string }
> => {
    const { t } = useTranslation();
    return {
        formName: 'personal-data-overview-form',
        messages: {
            error: t('Could not sent personal data request'),
            success: t('We sent an email with link to your personal data'),
        },
        fields: createFields<PersonalDataOverviewFormType>({
            email: t('Your email'),
        }),
    };
};

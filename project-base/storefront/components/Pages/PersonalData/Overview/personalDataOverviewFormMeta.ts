import { yupResolver } from '@hookform/resolvers/yup';
import useTranslation from 'next-translate/useTranslation';
import { useMemo } from 'react';
import { UseFormReturn } from 'react-hook-form';
import { PersonalDataOverviewFormType } from 'types/form';
import { useShopsysForm } from 'utils/forms/useShopsysForm';
import * as Yup from 'yup';

export const usePersonalDataOverviewForm = (): [
    UseFormReturn<PersonalDataOverviewFormType>,
    PersonalDataOverviewFormType,
] => {
    const { t } = useTranslation();
    const resolver = yupResolver(
        Yup.object().shape<Record<keyof PersonalDataOverviewFormType, any>>({
            email: Yup.string().required(t('This field is required')).email(t('This value is not a valid email')),
        }),
    );
    const defaultValues = { email: '' };

    return [useShopsysForm(resolver, defaultValues), defaultValues];
};

type PersonalDataOverviewFormMetaType = {
    formName: string;
    messages: {
        error: string;
        success: string;
    };
    fields: {
        [key in keyof PersonalDataOverviewFormType]: {
            name: key;
            label: string;
            errorMessage: string | undefined;
        };
    };
};

export const usePersonalDataOverviewFormMeta = (
    formProviderMethods: UseFormReturn<PersonalDataOverviewFormType>,
): PersonalDataOverviewFormMetaType => {
    const { t } = useTranslation();

    const formMeta = useMemo(
        () => ({
            formName: 'personal-data-overview-form',
            messages: {
                error: t('Could not sent personal data request'),
                success: t('We sent an email with link to your personal data'),
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

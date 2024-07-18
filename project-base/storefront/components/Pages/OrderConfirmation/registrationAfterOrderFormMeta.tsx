import { yupResolver } from '@hookform/resolvers/yup';
import { Link, linkPlaceholderTwClass } from 'components/Basic/Link/Link';
import { validatePassword, validatePrivacyPolicy } from 'components/Forms/validationRules';
import { useSettingsQuery } from 'graphql/requests/settings/queries/SettingsQuery.generated';
import Trans from 'next-translate/Trans';
import useTranslation from 'next-translate/useTranslation';
import { useMemo } from 'react';
import { UseFormReturn } from 'react-hook-form';
import { RegistrationAfterOrderFormType } from 'types/form';
import { useShopsysForm } from 'utils/forms/useShopsysForm';
import * as Yup from 'yup';

export const useRegistrationAfterOrderForm = (): [
    UseFormReturn<RegistrationAfterOrderFormType>,
    RegistrationAfterOrderFormType,
] => {
    const { t } = useTranslation();
    const resolver = yupResolver(
        Yup.object().shape<Record<keyof RegistrationAfterOrderFormType, any>>({
            password: validatePassword(t),
            privacyPolicy: validatePrivacyPolicy(t),
        }),
    );
    const defaultValues = { password: '', privacyPolicy: false };

    return [useShopsysForm(resolver, defaultValues), defaultValues];
};

type RegistrationAfterOrderFormMetaType = {
    formName: string;
    fields: {
        privacyPolicy: {
            name: 'privacyPolicy';
            label: string | JSX.Element;
            errorMessage: string | undefined;
        };
        password: {
            name: 'password';
            label: string;
            errorMessage: string | undefined;
        };
    };
};

export const useRegistrationAfterOrderFormMeta = (
    formProviderMethods: UseFormReturn<RegistrationAfterOrderFormType>,
): RegistrationAfterOrderFormMetaType => {
    const { t } = useTranslation();
    const [{ data: settingsData }] = useSettingsQuery();
    const termsAndConditionUrl = settingsData?.settings?.termsAndConditionsArticleUrl;
    const errors = formProviderMethods.formState.errors;

    const formMeta = useMemo(
        () => ({
            formName: 'registration-after-order-form',
            fields: {
                password: {
                    name: 'password' as const,
                    label: t('Password'),
                    errorMessage: errors.password?.message,
                },
                privacyPolicy: {
                    name: 'privacyPolicy' as const,
                    label: (
                        <Trans
                            defaultTrans="I agree with <lnk1>terms and conditions</lnk1> and privacy policy"
                            i18nKey="I agree with terms and conditions and privacy policy"
                            components={{
                                lnk1: termsAndConditionUrl ? (
                                    <Link isExternal href={termsAndConditionUrl} target="_blank" />
                                ) : (
                                    <span className={linkPlaceholderTwClass} />
                                ),
                            }}
                        />
                    ),
                    errorMessage: errors.privacyPolicy?.message,
                },
            },
        }),
        [errors.password?.message, errors.privacyPolicy?.message, termsAndConditionUrl, t],
    );

    return formMeta;
};

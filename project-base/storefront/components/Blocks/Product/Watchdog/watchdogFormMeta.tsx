import { yupResolver } from '@hookform/resolvers/yup';
import { Link, linkPlaceholderTwClass } from 'components/Basic/Link/Link';
import { validateEmail, validatePrivacyPolicy } from 'components/Forms/validationRules';
import { useSettingsQuery } from 'graphql/requests/settings/queries/SettingsQuery.generated';
import Trans from 'next-translate/Trans';
import useTranslation from 'next-translate/useTranslation';
import { useMemo } from 'react';
import { UseFormReturn } from 'react-hook-form';
import { WatchdogFormType } from 'types/form';
import { useShopsysForm } from 'utils/forms/useShopsysForm';
import * as Yup from 'yup';

export const useWatchdogForm = (
    defaultValues: WatchdogFormType,
): [UseFormReturn<WatchdogFormType>, WatchdogFormType | undefined] => {
    const { t } = useTranslation();

    const resolver = yupResolver(
        Yup.object().shape<Record<keyof WatchdogFormType, any>>({
            email: validateEmail(t),
            productUuid: Yup.string().required(),
            gdprAgreement: validatePrivacyPolicy(t),
        }),
    );

    return [useShopsysForm(resolver, defaultValues), defaultValues];
};

type WatchdogFormMetaType = {
    formName: string;
    messages: {
        error: string;
    };
    fields: {
        [key in keyof WatchdogFormType]: {
            name: key;
            label: string | JSX.Element;
            errorMessage?: string;
        };
    };
};

export const useWatchdogFormMeta = (formProviderMethods: UseFormReturn<WatchdogFormType>): WatchdogFormMetaType => {
    const { t } = useTranslation();
    const [{ data: settingsData }] = useSettingsQuery();
    const privacyPolicyArticleUrl = settingsData?.settings?.privacyPolicyArticleUrl;

    const errors = formProviderMethods.formState.errors;

    const formMeta = useMemo(
        () => ({
            formName: 'watchdog-form',
            messages: {
                error: t('An error occurred while creating your watchdog'),
            },
            fields: {
                email: {
                    name: 'email' as const,
                    label: t('Your email'),
                    errorMessage: errors.email?.message,
                },
                productUuid: {
                    name: 'productUuid' as const,
                    label: t('Product'),
                    errorMessage: errors.productUuid?.message,
                },
                gdprAgreement: {
                    name: 'gdprAgreement' as const,
                    label: (
                        <Trans
                            defaultTrans="I agree with <lnk1>processing of privacy policy</lnk1>."
                            i18nKey="GdprAgreementCheckbox"
                            components={{
                                lnk1: privacyPolicyArticleUrl ? (
                                    <Link
                                        isExternal
                                        aria-label={t('Go to privacy policy article')}
                                        className="inline text-sm"
                                        href={privacyPolicyArticleUrl}
                                        target="_blank"
                                    />
                                ) : (
                                    <span className={linkPlaceholderTwClass} />
                                ),
                            }}
                        />
                    ),
                    errorMessage: errors.gdprAgreement?.message,
                },
            },
        }),
        [errors.gdprAgreement?.message, errors.productUuid?.message, t],
    );

    return formMeta;
};

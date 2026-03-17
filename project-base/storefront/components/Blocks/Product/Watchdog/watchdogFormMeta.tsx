import { yupResolver } from '@hookform/resolvers/yup';
import { Link, linkPlaceholderTwClass } from 'components/Basic/Link/Link';
import { validateEmail, validatePrivacyPolicy } from 'components/Forms/validationRules';
import { useSettingsQuery } from 'graphql/requests/settings/queries/SettingsQuery.generated';
import Trans from 'next-translate/Trans';
import { UseFormReturn } from 'react-hook-form';
import { WatchdogFormType } from 'types/form';
import { FormMeta } from 'types/formMeta';
import { createFields } from 'utils/forms/createFields';
import { useFormWrapper } from 'utils/forms/useFormWrapper';
import useTranslation from 'utils/i18n/useTranslationWrapper';
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

    return [useFormWrapper(resolver, defaultValues), defaultValues];
};

export const useWatchdogFormMeta = (): FormMeta<WatchdogFormType, { error: string }> => {
    const { t } = useTranslation();
    const [{ data: settingsData }] = useSettingsQuery();
    const privacyPolicyArticleUrl = settingsData?.settings?.privacyPolicyArticleUrl;

    return {
        formName: 'watchdog-form',
        messages: {
            error: t('An error occurred while creating your watchdog'),
        },
        fields: createFields<WatchdogFormType>({
            email: t('Your email'),
            productUuid: t('Product'),
            gdprAgreement: (
                <Trans
                    defaultTrans="I agree with <lnk1>processing of privacy policy</lnk1>."
                    i18nKey="GdprAgreementCheckbox"
                    components={{
                        lnk1: privacyPolicyArticleUrl ? (
                            <Link
                                aria-label={t('Go to privacy policy article', { ns: 'accessibility' })}
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
        }),
    };
};
